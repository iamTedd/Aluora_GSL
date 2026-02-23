<?php
// Start session
session_start();

// Helper function to sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Helper function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Helper function to validate phone
function validatePhone($phone) {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return strlen($phone) >= 10;
}

// Process form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_form'])) {
    // Validate name
    if (empty($_POST['name'])) {
        $errors['name'] = 'Name is required';
    } elseif (strlen($_POST['name']) < 2) {
        $errors['name'] = 'Name must be at least 2 characters';
    }
    
    // Validate email
    if (empty($_POST['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!validateEmail($_POST['email'])) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    // Validate phone
    if (empty($_POST['phone'])) {
        $errors['phone'] = 'Phone number is required';
    } elseif (!validatePhone($_POST['phone'])) {
        $errors['phone'] = 'Please enter a valid phone number';
    }
    
    // Validate subject
    if (empty($_POST['subject'])) {
        $errors['subject'] = 'Please select a subject';
    }
    
    // Validate message
    if (empty($_POST['message'])) {
        $errors['message'] = 'Message is required';
    } elseif (strlen($_POST['message']) < 10) {
        $errors['message'] = 'Message must be at least 10 characters';
    }
    
    // Validate consent
    if (!isset($_POST['consent'])) {
        $errors['consent'] = 'Please agree to the privacy policy';
    }
    
    // If no errors, process the form
    if (empty($errors)) {
        // Sanitize inputs
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $subject = sanitize($_POST['subject']);
        $message = sanitize($_POST['message']);
        $company = sanitize($_POST['company'] ?? '');
        
        // In a production environment, you would:
        // 1. Store in database
        // 2. Send email notification
        // 3. Integrate with CRM
        
        // For demonstration, we'll simulate success
        $success = true;
        
        // Store in session for display
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Thank you for contacting us! We have received your message and will get back to you within 24 hours.'
        ];
        
        // Clear form data
        unset($_POST);
        
        // Redirect to prevent form resubmission
        header('Location: contact.php');
        exit;
    }
}

// Display session message
$message = '';
$messageType = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message']['text'];
    $messageType = $_SESSION['message']['type'];
    unset($_SESSION['message']);
}

// Get current page
$current_page = 'contact';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Contact Aluora General Suppliers Limited - Get in touch for product inquiries, quotes, and customer support.">
    <meta name="keywords" content="contact us, general suppliers, Kenya, customer support, get quote">
    <meta name="author" content="Aluora General Suppliers Limited">
    <title>Contact Us - Aluora General Suppliers Limited</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/enhanced.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">
                        <span class="logo-icon"><i class="fas fa-box-open"></i></span>
                        <span class="logo-text">Aluora<span>GSL</span></span>
                    </a>
                </div>
                
                <nav class="nav-menu">
                    <ul>
                        <li><a href="index.php" class="<?php echo $current_page == 'index' ? 'active' : ''; ?>">Home</a></li>
                        <li><a href="about.php" class="<?php echo $current_page == 'about' ? 'active' : ''; ?>">About Us</a></li>
                        <li><a href="products.php" class="<?php echo $current_page == 'products' ? 'active' : ''; ?>">Products</a></li>
                        <li><a href="contact.php" class="<?php echo $current_page == 'contact' ? 'active' : ''; ?>">Contact</a></li>
                    </ul>
                </nav>
                
                <div class="header-cta">
                    <a href="contact.php" class="btn btn-primary">Get Quote</a>
                </div>
                
                <button class="mobile-menu-btn" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div class="mobile-menu">
            <ul>
                <li><a href="index.php" class="<?php echo $current_page == 'index' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="about.php" class="<?php echo $current_page == 'about' ? 'active' : ''; ?>">About Us</a></li>
                <li><a href="products.php" class="<?php echo $current_page == 'products' ? 'active' : ''; ?>">Products</a></li>
                <li><a href="contact.php" class="<?php echo $current_page == 'contact' ? 'active' : ''; ?>">Contact</a></li>
            </ul>
        </div>
    </header>

    <!-- Page Banner -->
    <section class="page-banner">
        <div class="container">
            <h1>Contact Us</h1>
            <p>We'd love to hear from you. Get in touch with our team</p>
            <div class="breadcrumb">
                <a href="index.php">Home</a>
                <span>/</span>
                <span>Contact Us</span>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>
            
            <div class="contact-content">
                <!-- Contact Info -->
                <div class="contact-info-card">
                    <h2>Get in Touch</h2>
                    <p>Have a question about our products or services? Need a custom quote? Our team is here to help you with all your general supply needs.</p>
                    
                    <div class="contact-methods">
                        <div class="contact-method">
                            <div class="method-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="method-details">
                                <h4>Phone</h4>
                                <p>+254-715-173-207</p>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="method-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="method-details">
                                <h4>Email</h4>
                                <p>aluoragsl@gmail.com</p>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="method-icon">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div class="method-details">
                                <h4>Website</h4>
                                <p>www.aluoragsl.com</p>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="method-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="method-details">
                                <h4>Business Hours</h4>
                                <p>Mon - Fri: 8:00 AM - 6:00 PM<br>Sat: 9:00 AM - 2:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="contact-form-wrapper">
                    <h2>Send Us a Message</h2>
                    <p>Fill out the form below and we'll get back to you within 24 hours.</p>
                    
                    <form action="contact.php" method="POST" class="contact-form" id="contactForm">
                        <input type="hidden" name="contact_form" value="1">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name *</label>
                                <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required placeholder="Enter your full name">
                                <?php if (isset($errors['name'])): ?>
                                <span class="error-text"><?php echo htmlspecialchars($errors['name']); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="company">Company Name</label>
                                <input type="text" id="company" name="company" value="<?php echo isset($_POST['company']) ? htmlspecialchars($_POST['company']) : ''; ?>" placeholder="Enter your company name (optional)">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required placeholder="Enter your email address">
                                <?php if (isset($errors['email'])): ?>
                                <span class="error-text"><?php echo htmlspecialchars($errors['email']); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required placeholder="Enter your phone number">
                                <?php if (isset($errors['phone'])): ?>
                                <span class="error-text"><?php echo htmlspecialchars($errors['phone']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <select id="subject" name="subject" required>
                                <option value="">Select a subject</option>
                                <option value="Product Inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Product Inquiry') ? 'selected' : ''; ?>>Product Inquiry</option>
                                <option value="Request Quote" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Request Quote') ? 'selected' : ''; ?>>Request Quote</option>
                                <option value="Order Status" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Order Status') ? 'selected' : ''; ?>>Order Status</option>
                                <option value="Customer Support" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Customer Support') ? 'selected' : ''; ?>>Customer Support</option>
                                <option value="Partnership" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Partnership') ? 'selected' : ''; ?>>Partnership Opportunity</option>
                                <option value="Other" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <?php if (isset($errors['subject'])): ?>
                            <span class="error-text"><?php echo htmlspecialchars($errors['subject']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" rows="5" required placeholder="Tell us about your requirements..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                            <?php if (isset($errors['message'])): ?>
                            <span class="error-text"><?php echo htmlspecialchars($errors['message']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="consent" value="1" <?php echo isset($_POST['consent']) ? 'checked' : ''; ?>>
                                <span>I agree to the <a href="#" target="_blank">Privacy Policy</a> and consent to the processing of my personal data.</span>
                            </label>
                            <?php if (isset($errors['consent'])): ?>
                            <span class="error-text"><?php echo htmlspecialchars($errors['consent']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-submit">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <div style="background: var(--off-white); height: 400px; border-radius: var(--border-radius); display: flex; align-items: center; justify-content: center; flex-direction: column; gap: 15px;">
                <i class="fas fa-map-marker-alt" style="font-size: 3rem; color: var(--primary-color);"></i>
                <p style="color: var(--text-light); text-align: center;">
                    <strong>Location:</strong> Kenya<br>
                    <span style="font-size: 0.9rem;">Serving clients across Kenya and East Africa</span>
                </p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
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
                        <p>Your trusted partner for high-quality general supplies. We deliver excellence in every product and service we offer.</p>
                        <div class="footer-social">
                            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    
                    <div class="footer-links">
                        <h4>Quick Links</h4>
                        <ul>
                            <li><a href="index.php">Home</a></li>
                            <li><a href="about.php">About Us</a></li>
                            <li><a href="products.php">Products</a></li>
                            <li><a href="contact.php">Contact</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-links">
                        <h4>Products</h4>
                        <ul>
                            <li><a href="products.php#office">Office Supplies</a></li>
                            <li><a href="products.php#cleaning">Cleaning Supplies</a></li>
                            <li><a href="products.php#safety">Safety Equipment</a></li>
                            <li><a href="products.php#electronics">Electronics</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-contact">
                        <h4>Contact Us</h4>
                        <ul>
                            <li>
                                <i class="fas fa-phone"></i>
                                <span>+254-715-173-207</span>
                            </li>
                            <li>
                                <i class="fas fa-envelope"></i>
                                <span>aluoragsl@gmail.com</span>
                            </li>
                            <li>
                                <i class="fas fa-globe"></i>
                                <span>www.aluoragsl.com</span>
                            </li>
                        </ul>
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
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button class="back-to-top" aria-label="Back to top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- JavaScript -->
    <script src="js/main.js"></script>
</body>
</html>