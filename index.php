<?php
// Include configuration
require_once 'config.php';

// Get current page
$current_page = 'home';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        $pdo = getDBConnection();
        
        switch ($_POST['action']) {
            // Newsletter subscription
            case 'newsletter':
                $email = sanitize($_POST['email']);
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo jsonResponse(['success' => false, 'message' => 'Invalid email address']);
                }
                $stmt = $pdo->prepare("INSERT INTO newsletters (email) VALUES (?) ON DUPLICATE KEY UPDATE subscribed = 1");
                $stmt->execute([$email]);
                echo jsonResponse(['success' => true, 'message' => 'Successfully subscribed to newsletter!']);
                break;
            
            // Quick order
            case 'quick_order':
                if (!isLoggedIn()) {
                    echo jsonResponse(['success' => false, 'message' => 'Please login to place orders']);
                }
                $product_id = (int)$_POST['product_id'];
                $quantity = (int)$_POST['quantity'];
                
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch();
                
                if (!$product) {
                    echo jsonResponse(['success' => false, 'message' => 'Product not found']);
                }
                
                // Create order
                $order_number = generateOrderNumber();
                $total = $product['price'] * $quantity;
                
                $stmt = $pdo->prepare("INSERT INTO orders (order_number, user_id, total, status) VALUES (?, ?, ?, 'pending')");
                $stmt->execute([$order_number, $_SESSION['user_id'], $total]);
                $order_id = $pdo->lastInsertId();
                
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$order_id, $product_id, $product['name'], $product['price'], $quantity, $total]);
                
                echo jsonResponse(['success' => true, 'message' => 'Order placed successfully!', 'order_number' => $order_number]);
                break;
            
            // Chat message (AI or Human)
            case 'chat':
                $message = sanitize($_POST['message']);
                $session_id = session_id();
                $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
                
                // Check if user wants human
                $wants_human = in_array(strtolower($message), ['talk to human', 'connect to agent', 'need human help', 'speak to human']);
                
                if ($wants_human) {
                    $response = "I'll connect you with a human support agent right away. Please wait while I transfer you...";
                    $is_from_ai = 0;
                    $is_from_human = 1;
                    
                    // Log the request
                    $stmt = $pdo->prepare("INSERT INTO chat_messages (session_id, user_id, message, response, is_from_ai, is_from_human) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$session_id, $user_id, $message, $response, 0, 1]);
                    
                    // Create support ticket
                    $ticket_number = generateTicketNumber();
                    $stmt = $pdo->prepare("INSERT INTO support_tickets (ticket_number, user_id, subject, category, priority, status) VALUES (?, ?, ?, 'general', 'high', 'open')");
                    $stmt->execute([$ticket_number, $user_id, 'Customer requested human assistance']);
                    
                    echo jsonResponse([
                        'success' => true, 
                        'message' => $response,
                        'human_transfer' => true,
                        'ticket_number' => $ticket_number,
                        'waiting_message' => 'A support agent will be with you shortly. In the meantime, please provide details about your issue.'
                    ]);
                } else {
                    // Get AI response
                    $response = getAIResponse($message);
                    
                    // Save message
                    $stmt = $pdo->prepare("INSERT INTO chat_messages (session_id, user_id, message, response, is_from_ai) VALUES (?, ?, ?, ?, 1)");
                    $stmt->execute([$session_id, $user_id, $message, $response]);
                    
                    echo jsonResponse(['success' => true, 'message' => $response, 'from_ai' => true]);
                }
                break;
            
            // Submit tender
            case 'submit_tender':
                if (!isLoggedIn()) {
                    echo jsonResponse(['success' => false, 'message' => 'Please login to submit a tender']);
                    exit;
                }
                
                $title = sanitize($_POST['title']);
                $description = sanitize($_POST['description']);
                $category = sanitize($_POST['category']);
                $quantity = sanitize($_POST['quantity']);
                $budget = sanitize($_POST['budget']);
                $deadline = sanitize($_POST['deadline']);
                
                if (empty($title) || empty($description)) {
                    echo jsonResponse(['success' => false, 'message' => 'Please fill in all required fields']);
                    exit;
                }
                
                try {
                    $tender_number = generateTenderNumber();
                    $stmt = $pdo->prepare("INSERT INTO tenders (tender_number, user_id, title, description, category, quantity, budget_range, deadline, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
                    $stmt->execute([$tender_number, $_SESSION['user_id'], $title, $description, $category, $quantity, $budget, $deadline]);
                    
                    echo jsonResponse(['success' => true, 'message' => 'Tender submitted successfully!', 'tender_number' => $tender_number]);
                } catch (Exception $e) {
                    echo jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                }
                exit;
                break;
            
            // Rate chat
            case 'rate_chat':
                $chat_id = (int)$_POST['chat_id'];
                $rating = (int)$_POST['rating'];
                $stmt = $pdo->prepare("UPDATE chat_messages SET rating = ? WHERE id = ?");
                $stmt->execute([$rating, $chat_id]);
                echo jsonResponse(['success' => true, 'message' => 'Thank you for your feedback!']);
                break;
                
            default:
                echo jsonResponse(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        error_log("AJAX Error: " . $e->getMessage());
        echo jsonResponse(['success' => false, 'message' => 'An error occurred. Please try again.']);
    }
    exit;
}

// Get featured products
try {
    $pdo = getDBConnection();
    $featured_products = [];
    $categories = [];
    
    if ($pdo) {
        $stmt = $pdo->query("SELECT * FROM products WHERE featured = 1 AND status = 'active' LIMIT 8");
        $featured_products = $stmt->fetchAll();
        
        $stmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order");
        $categories = $stmt->fetchAll();
    }
} catch (Exception $e) {
    error_log("Error fetching products: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aluora General Suppliers Limited - Your trusted partner for high-quality general supplies. Office supplies, cleaning products, safety equipment, and more.">
    <meta name="keywords" content="general supplies, office supplies, cleaning products, safety equipment, industrial tools, electronics, Kenya">
    <title>Aluora General Suppliers Limited - Quality General Supplies</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/enhanced.css">
</head>
<body class="<?php echo isLoggedIn() ? 'user-logged-in' : ''; ?>">
    <!-- Preloader -->
    <div class="preloader">
        <div class="preloader-content">
            <div class="logo-loader">
                <i class="fas fa-box-open"></i>
            </div>
            <div class="loader-bar">
                <div class="loader-progress"></div>
            </div>
            <p>Loading excellence...</p>
        </div>
    </div>

    <!-- Header -->
    <header class="header" id="mainHeader">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">
                        <span class="logo-icon"><i class="fas fa-box-open"></i></span>
                        <span class="logo-text">Aluora<span>GSL</span></span>
                    </a>
                </div>
                
                <div class="search-bar">
                    <input type="text" placeholder="Search products, categories..." id="globalSearch">
                    <button><i class="fas fa-search"></i></button>
                    <div class="search-results" id="searchResults"></div>
                </div>
                
                <nav class="nav-menu">
                    <ul>
                        <li><a href="index.php" class="active">Home</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="#" onclick="openModal('tenderModal'); return false;">Tenders</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </nav>
                
                <div class="header-actions">
                    <?php if (isLoggedIn()): ?>
                    <div class="notification-bell" onclick="openModal('notificationsModal')">
                        <i class="fas fa-bell"></i>
                        <span class="badge" id="notificationBadge">0</span>
                    </div>
                    <div class="user-menu">
                        <button class="user-btn">
                            <div class="user-avatar"><?php echo substr($_SESSION['first_name'], 0, 1); ?></div>
                            <span><?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="user-dropdown">
                            <a href="dashboard.php"><i class="fas fa-dashboard"></i> Dashboard</a>
                            <a href="dashboard.php?section=orders"><i class="fas fa-shopping-bag"></i> My Orders</a>
                            <a href="dashboard.php?section=tenders"><i class="fas fa-file-contract"></i> My Tenders</a>
                            <a href="dashboard.php?section=tickets"><i class="fas fa-ticket-alt"></i> Support Tickets</a>
                            <hr>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                    <?php else: ?>
                    <button class="btn btn-outline" onclick="openModal('loginModal')">Login</button>
                    <button class="btn btn-primary" onclick="openModal('registerModal')">Register</button>
                    <?php endif; ?>
                </div>
                
                <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div class="mobile-menu" id="mobileMenu">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="#" onclick="openModal('tenderModal'); return false;">Tenders</a></li>
                <li><a href="contact.php">Contact</a></li>
                <?php if (isLoggedIn()): ?>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                <li><button class="btn btn-primary" onclick="openModal('loginModal')">Login</button></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="hero">
        <div class="hero-bg-elements">
            <div class="floating-shape shape-1"></div>
            <div class="floating-shape shape-2"></div>
            <div class="floating-shape shape-3"></div>
            <div class="grid-pattern"></div>
        </div>
        
        <div class="container">
            <div class="hero-content">
                <div class="hero-badge">
                    <span class="pulse-dot"></span>
                    <span>Trusted by 200+ Businesses</span>
                </div>
                
                <h1>Your Partner for <span class="gradient-text">Quality Supplies</span></h1>
                <p>From office essentials to industrial machinery, we deliver excellence across Kenya. Experience premium products with unmatched service.</p>
                
                <div class="hero-actions">
                    <button class="btn btn-primary btn-lg" onclick="scrollToProducts()">
                        <i class="fas fa-store"></i> Browse Products
                    </button>
                    <button class="btn btn-outline btn-lg" onclick="openModal('tenderModal')">
                        <i class="fas fa-file-contract"></i> Submit Tender
                    </button>
                </div>
                
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-icon"><i class="fas fa-box"></i></div>
                        <div class="stat-info">
                            <span class="stat-number" data-count="500">0</span>
                            <span class="stat-label">Products</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-info">
                            <span class="stat-number" data-count="200">0</span>
                            <span class="stat-label">Clients</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon"><i class="fas fa-industry"></i></div>
                        <div class="stat-info">
                            <span class="stat-number" data-count="50">0</span>
                            <span class="stat-label">Industries</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon"><i class="fas fa-trophy"></i></div>
                        <div class="stat-info">
                            <span class="stat-number" data-count="98">0</span>
                            <span class="stat-label">% Satisfaction</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="hero-visual">
                <div class="hero-image-container">
                    <div class="hero-image">
                        <img src="https://images.unsplash.com/photo-1553413077-190dd305871c?w=600" alt="Quality Supplies">
                    </div>
                    <div class="floating-card card-1">
                        <i class="fas fa-check-circle"></i>
                        <span>ISO 9001 Certified</span>
                    </div>
                    <div class="floating-card card-2">
                        <i class="fas fa-truck"></i>
                        <span>Fast Delivery</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="scroll-indicator" onclick="scrollToProducts()">
            <span>Scroll to explore</span>
            <div class="mouse">
                <div class="wheel"></div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Our Products</span>
                <h2>Browse by Category</h2>
                <p>Find exactly what you need from our extensive collection</p>
            </div>
            
            <div class="categories-grid">
                <?php 
                $cat_icons = ['fa-briefcase', 'fa-sparkles', 'fa-shield-halved', 'fa-tools', 'fa-microchip', 'fa-utensils', 'fa-store'];
                $cat_colors = ['#1a5f4a', '#c9a227', '#e74c3c', '#3498db', '#9b59b6', '#e67e22', '#1abc9c'];
                $i = 0;
                foreach ($categories as $cat): 
                ?>
                <a href="products.php?category=<?php echo $cat['slug']; ?>" class="category-card">
                    <div class="category-icon" style="background: <?php echo $cat_colors[$i % count($cat_colors)]; ?>">
                        <i class="fas <?php echo $cat_icons[$i % count($cat_icons)]; ?>"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                    <p><?php echo htmlspecialchars($cat['description'] ?? 'Quality products'); ?></p>
                    <span class="category-arrow"><i class="fas fa-arrow-right"></i></span>
                </a>
                <?php $i++; endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="products-showcase" id="products">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Featured</span>
                <h2>Popular Products</h2>
                <p>Top-rated products trusted by businesses across Kenya</p>
            </div>
            
            <div class="products-grid" id="productsGrid">
                <!-- Products loaded via JavaScript -->
                <div class="product-skeleton"></div>
                <div class="product-skeleton"></div>
                <div class="product-skeleton"></div>
                <div class="product-skeleton"></div>
            </div>
            
            <div class="section-cta">
                <a href="products.php" class="btn btn-primary">View All Products <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-full">
        <div class="container">
            <div class="services-content">
                <div class="services-info">
                    <span class="section-tag">Why Choose Us</span>
                    <h2>Experience the Aluora GSL Advantage</h2>
                    <p>We're committed to being your one-stop solution for all general supplies needs, delivering excellence in every interaction.</p>
                    
                    <div class="service-features">
                        <div class="feature-box">
                            <div class="feature-icon"><i class="fas fa-tags"></i></div>
                            <div class="feature-text">
                                <h4>Competitive Pricing</h4>
                                <p>Best prices without compromising quality</p>
                            </div>
                        </div>
                        <div class="feature-box">
                            <div class="feature-icon"><i class="fas fa-shipping-fast"></i></div>
                            <div class="feature-text">
                                <h4>Fast Delivery</h4>
                                <p>Same-day delivery in Nairobi</p>
                            </div>
                        </div>
                        <div class="feature-box">
                            <div class="feature-icon"><i class="fas fa-headset"></i></div>
                            <div class="feature-text">
                                <h4>24/7 Support</h4>
                                <p>Always here to help you</p>
                            </div>
                        </div>
                        <div class="feature-box">
                            <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                            <div class="feature-text">
                                <h4>Quality Guaranteed</h4>
                                <p>ISO 9001 certified products</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="services-image">
                    <div class="image-stack-enhanced">
                        <img src="https://images.unsplash.com/photo-1586495777744-4413f21062fa?w=500" alt="Services">
                        <div class="stats-overlay">
                            <div class="stat">
                                <span class="number">98%</span>
                                <span class="label">Customer Satisfaction</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="quick-actions">
        <div class="container">
            <div class="actions-grid">
                <div class="action-card tender-card" onclick="openModal('tenderModal')">
                    <div class="action-icon"><i class="fas fa-file-contract"></i></div>
                    <h3>Submit Tender</h3>
                    <p>Request a quote for bulk orders or corporate procurement</p>
                    <span class="action-btn">Get Started <i class="fas fa-arrow-right"></i></span>
                </div>
                
                <div class="action-card quote-card" onclick="window.location.href='products.php?action=quote'">
                    <div class="action-icon"><i class="fas fa-calculator"></i></div>
                    <h3>Request Quote</h3>
                    <p>Get custom pricing for your specific requirements</p>
                    <span class="action-btn">Get Started <i class="fas fa-arrow-right"></i></span>
                </div>
                
                <div class="action-card support-card" onclick="toggleChat()">
                    <div class="action-icon"><i class="fas fa-comments"></i></div>
                    <h3>AI Assistant</h3>
                    <p>Get instant answers or chat with our support team</p>
                    <span class="action-btn">Start Chat <i class="fas fa-arrow-right"></i></span>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Testimonials</span>
                <h2>What Our Clients Say</h2>
                <p>Trusted by businesses across Kenya</p>
            </div>
            
            <div class="testimonials-slider">
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="client-avatar">JM</div>
                        <div class="client-info">
                            <h4>James Mwangi</h4>
                            <span>CEO, TechCorp Kenya</span>
                        </div>
                        <div class="rating">★★★★★</div>
                    </div>
                    <p>"Aluora GSL has been our go-to supplier for office equipment. Their quality and delivery speed are unmatched. Highly recommended!"</p>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="client-avatar">SK</div>
                        <div class="client-info">
                            <h4>Sarah Kimani</h4>
                            <span>Procurement Manager, Hotel Paradise</span>
                        </div>
                        <div class="rating">★★★★★</div>
                    </div>
                    <p>"Excellent hospitality supplies. The quality of their products has helped us maintain our 5-star standards. Great customer service!"</p>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="client-avatar">DO</div>
                        <div class="client-info">
                            <h4>David Ochieng</h4>
                            <span>Director, BuildRight Construction</span>
                        </div>
                        <div class="rating">★★★★★</div>
                    </div>
                    <p>"Their industrial tools and safety equipment are top-notch. Aluora understands the needs of construction companies perfectly."</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-enhanced">
        <div class="container">
            <div class="cta-wrapper">
                <div class="cta-content">
                    <h2>Ready to Transform Your Business?</h2>
                    <p>Join hundreds of satisfied businesses who trust Aluora GSL for their supply needs.</p>
                    
                    <div class="cta-features">
                        <div class="cta-feature">
                            <i class="fas fa-check"></i>
                            <span>Free account setup</span>
                        </div>
                        <div class="cta-feature">
                            <i class="fas fa-check"></i>
                            <span>Instant quotes</span>
                        </div>
                        <div class="cta-feature">
                            <i class="fas fa-check"></i>
                            <span>Priority support</span>
                        </div>
                    </div>
                    
                    <div class="cta-buttons">
                        <?php if (!isLoggedIn()): ?>
                        <button class="btn btn-white btn-lg" onclick="openModal('registerModal')">Create Account</button>
                        <button class="btn btn-outline-white btn-lg" onclick="openModal('tenderModal')">Submit Tender</button>
                        <?php else: ?>
                        <a href="dashboard.php" class="btn btn-white btn-lg">Go to Dashboard</a>
                        <button class="btn btn-outline-white btn-lg" onclick="openModal('tenderModal')">New Tender</button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="cta-visual">
                    <div class="cta-shapes">
                        <div class="shape shape-1"></div>
                        <div class="shape shape-2"></div>
                        <div class="shape shape-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-enhanced">
        <div class="footer-main">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-about">
                        <div class="footer-logo">
                            <a href="index.php">
                                <span class="logo-icon"><i class="fas fa-box-open"></i></span>
                                <span class="logo-text">Aluora<span>GSL</span></span>
                            </a>
                        </div>
                        <p>Your trusted partner for high-quality general supplies. Delivering excellence across Kenya since 2020.</p>
                        
                        <div class="footer-contact-info">
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <span>+254-715-173-207</span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <span>aluoragsl@gmail.com</span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-globe"></i>
                                <span>www.aluoragsl.com</span>
                            </div>
                        </div>
                        
                        <div class="footer-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    
                    <div class="footer-links">
                        <h4>Quick Links</h4>
                        <ul>
                            <li><a href="index.php">Home</a></li>
                            <li><a href="about.php">About Us</a></li>
                            <li><a href="products.php">Products</a></li>
                            <li><a href="#" onclick="openModal('tenderModal'); return false;">Submit Tender</a></li>
                            <li><a href="contact.php">Contact</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-links">
                        <h4>Products</h4>
                        <ul>
                            <li><a href="products.php?category=office-supplies">Office Supplies</a></li>
                            <li><a href="products.php?category=cleaning-janitorial">Cleaning Supplies</a></li>
                            <li><a href="products.php?category=safety-equipment">Safety Equipment</a></li>
                            <li><a href="products.php?category=electronics-tech">Electronics</a></li>
                            <li><a href="products.php?category=industrial-tools">Industrial Tools</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-newsletter">
                        <h4>Newsletter</h4>
                        <p>Subscribe for updates on new products and special offers.</p>
                        <form class="newsletter-form" onsubmit="subscribeNewsletter(event)">
                            <input type="email" placeholder="Enter your email" required>
                            <button type="submit"><i class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="container">
                <div class="footer-bottom-content">
                    <p>&copy; <?php echo date('Y'); ?> Aluora General Suppliers Limited. All Rights Reserved.</p>
                    <div class="footer-bottom-links">
                        <a href="#">Privacy Policy</a>
                        <a href="#">Terms of Service</a>
                        <a href="#">Sitemap</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Chat Widget -->
    <div class="chat-widget" id="chatWidget">
        <button class="chat-toggle" onclick="toggleChat()">
            <i class="fas fa-comments"></i>
            <span class="chat-badge">AI</span>
        </button>
        
        <div class="chat-container" id="chatContainer">
            <div class="chat-header">
                <div class="chat-title">
                    <i class="fas fa-robot"></i>
                    <span>AI Assistant</span>
                </div>
                <button class="chat-close" onclick="toggleChat()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="chat-messages" id="chatMessages">
                <div class="chat-message bot">
                    <div class="message-content">
                        <p>Hello! I'm your AI assistant at Aluora GSL. How can I help you today?</p>
                        <p style="margin-top: 10px; font-size: 0.85rem; color: #666;">💡 Try asking about:</p>
                        <ul style="margin-left: 15px; font-size: 0.85rem;">
                            <li>Our products</li>
                            <li>Placing orders</li>
                            <li>Submitting tenders</li>
                            <li>Delivery information</li>
                        </ul>
                        <p style="margin-top: 10px; font-size: 0.85rem;">Or say <strong>"Talk to human"</strong> to connect with our support team.</p>
                    </div>
                </div>
            </div>
            
            <div class="chat-input">
                <input type="text" id="chatInput" placeholder="Type your message..." onkeypress="handleChatKeyPress(event)">
                <button onclick="sendChatMessage()"><i class="fas fa-paper-plane"></i></button>
            </div>
            
            <div class="chat-footer">
                <span>Powered by Aluora GSL AI</span>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <div class="modal" id="loginModal">
        <div class="modal-content modal-small">
            <button class="modal-close" onclick="closeModal('loginModal')"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h2>Welcome Back</h2>
                <p>Login to your account</p>
            </div>
            <form onsubmit="handleLogin(event)">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="Enter your email">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Enter your password">
                </div>
                <div class="form-footer">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="#" onclick="openModal('forgotModal'); return false;">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Login</button>
            </form>
            <div class="modal-footer-text">
                <p>Don't have an account? <a href="#" onclick="openModal('registerModal'); return false;">Register here</a></p>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal" id="registerModal">
        <div class="modal-content modal-small">
            <button class="modal-close" onclick="closeModal('registerModal')"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h2>Create Account</h2>
                <p>Join Aluora GSL today</p>
            </div>
            <form onsubmit="handleRegister(event)">
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" required placeholder="First name">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" required placeholder="Last name">
                    </div>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="Enter your email">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" required placeholder="+254...">
                </div>
                <div class="form-group">
                    <label>Company (Optional)</label>
                    <input type="text" name="company" placeholder="Your company name">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Create a password" minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required placeholder="Confirm your password">
                </div>
                <label class="checkbox-label">
                    <input type="checkbox" name="terms" required>
                    <span>I agree to the <a href="#">Terms & Privacy Policy</a></span>
                </label>
                <button type="submit" class="btn btn-primary btn-full">Create Account</button>
            </form>
            <div class="modal-footer-text">
                <p>Already have an account? <a href="#" onclick="openModal('loginModal'); return false;">Login here</a></p>
            </div>
        </div>
    </div>

    <!-- Tender Modal -->
    <div class="modal" id="tenderModal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('tenderModal')"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h2><i class="fas fa-file-contract"></i> Submit Tender</h2>
                <p>Request a quote for bulk orders or corporate procurement</p>
            </div>
            <form onsubmit="submitTender(event)">
                <div class="form-group">
                    <label>Company/Organization Name</label>
                    <input type="text" name="company_name" required placeholder="Your company name">
                </div>
                <div class="form-group">
                    <label>Contact Person</label>
                    <input type="text" name="contact_person" required placeholder="Your full name">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" required placeholder="your@email.com">
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" required placeholder="+254...">
                    </div>
                </div>
                <div class="form-group">
                    <label>Tender Title *</label>
                    <input type="text" name="title" required placeholder="What do you need?">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category">
                            <option value="">Select category</option>
                            <option value="office">Office Supplies</option>
                            <option value="cleaning">Cleaning Supplies</option>
                            <option value="safety">Safety Equipment</option>
                            <option value="industrial">Industrial Tools</option>
                            <option value="electronics">Electronics</option>
                            <option value="hospitality">Hospitality</option>
                            <option value="retail">Retail</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Estimated Quantity</label>
                        <input type="text" name="quantity" placeholder="e.g., 100 pieces">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Budget Range</label>
                        <input type="text" name="budget" placeholder="e.g., KES 50,000 - 100,000">
                    </div>
                    <div class="form-group">
                        <label>Deadline</label>
                        <input type="date" name="deadline">
                    </div>
                </div>
                <div class="form-group">
                    <label>Detailed Requirements *</label>
                    <textarea name="description" rows="4" required placeholder="Describe your requirements in detail..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Submit Tender</button>
            </form>
        </div>
    </div>

    <!-- Notifications Modal -->
    <div class="modal" id="notificationsModal">
        <div class="modal-content modal-medium">
            <button class="modal-close" onclick="closeModal('notificationsModal')"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h2>Notifications</h2>
            </div>
            <div class="notifications-list" id="notificationsList">
                <div class="notification-empty">
                    <i class="fas fa-bell-slash"></i>
                    <p>No new notifications</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick View Modal -->
    <div class="modal" id="quickViewModal">
        <div class="modal-content modal-medium" id="quickViewContent">
            <!-- Content loaded via JavaScript -->
        </div>
    </div>

    <!-- Back to Top -->
    <button class="back-to-top" id="backToTop" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- JavaScript -->
    <script src="js/main.js"></script>
    <script src="js/enhanced.js"></script>
</body>
</html>