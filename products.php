<?php
// Start session
session_start();

// Require config for database connection
require_once 'config.php';

// Get current page
$current_page = 'products';

// Fetch categories and products from database
try {
    $pdo = getDBConnection();
    
    // Get active categories
    $stmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order, name");
    $db_categories = $stmt->fetchAll();
    
    // Get active products
    $stmt = $pdo->query("SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 'active' 
        ORDER BY p.created_at DESC");
    $db_products = $stmt->fetchAll();
    
    // Group products by category
    $products_by_category = [];
    foreach ($db_products as $product) {
        $cat_slug = $product['category_slug'] ?? 'uncategorized';
        if (!isset($products_by_category[$cat_slug])) {
            $products_by_category[$cat_slug] = [
                'title' => $product['category_name'] ?? 'Uncategorized',
                'icon' => 'fa-folder',
                'description' => 'Products in this category',
                'items' => []
            ];
        }
        $products_by_category[$cat_slug]['items'][] = $product;
    }
    
    $products = $products_by_category;
    
} catch (Exception $e) {
    // Fallback to empty if database error
    $products = [];
    $db_categories = [];
    error_log("Products page error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
        'title' => 'Office Supplies',
        'icon' => 'fa-briefcase',
        'description' => 'Everything your workspace needs to function efficiently',
        'items' => [
            ['name' => 'Stationery Set', 'desc' => 'Premium pens, pencils, markers and more', 'price' => 'From KES 2,500'],
            ['name' => 'Paper Products', 'desc' => 'A4 paper, notebooks, sticky notes', 'price' => 'From KES 1,200'],
            ['name' => 'Office Furniture', 'desc' => 'Desks, chairs, cabinets and storage', 'price' => 'From KES 15,000'],
            ['name' => 'Computer Peripherals', 'desc' => 'Keyboards, mice, webcams, USB drives', 'price' => 'From KES 3,000'],
            ['name' => 'Filing & Organization', 'desc' => 'File folders, binders, trays', 'price' => 'From KES 1,500'],
            ['name' => 'Office Equipment', 'desc' => 'Printers, scanners, laminators', 'price' => 'From KES 25,000']
        ]
    ],
    'cleaning' => [
        'title' => 'Cleaning & Janitorial',
        'icon' => 'fa-sparkles',
        'description' => 'Keep your spaces clean and hygienic',
        'items' => [
            ['name' => 'Cleaning Chemicals', 'desc' => 'Detergents, disinfectants, sanitizers', 'price' => 'From KES 800'],
            ['name' => 'Cleaning Equipment', 'desc' => 'Mops, brooms, vacuum cleaners', 'price' => 'From KES 5,000'],
            ['name' => 'Disposables', 'desc' => 'Paper towels, tissues, toilet paper', 'price' => 'From KES 500'],
            ['name' => 'Hygiene Products', 'desc' => 'Hand sanitizers, soap dispensers', 'price' => 'From KES 1,000'],
            ['name' => 'Floor Care', 'desc' => 'Polishes, waxes, floor cleaners', 'price' => 'From KES 1,200'],
            ['name' => 'Waste Management', 'desc' => 'Bins, trash bags, recycling containers', 'price' => 'From KES 800']
        ]
    ],
    'safety' => [
        'title' => 'Safety Equipment',
        'icon' => 'fa-shield-halved',
        'description' => 'Protect your workforce with quality safety gear',
        'items' => [
            ['name' => 'Personal Protective Equipment', 'desc' => 'Helmets, gloves, safety glasses', 'price' => 'From KES 1,500'],
            ['name' => 'First Aid Supplies', 'desc' => 'Kits, bandages, antiseptic solutions', 'price' => 'From KES 2,000'],
            ['name' => 'Safety Signage', 'desc' => 'Warning signs, safety posters', 'price' => 'From KES 500'],
            ['name' => 'Fire Safety', 'desc' => 'Extinguishers, fire blankets, alarms', 'price' => 'From KES 5,000'],
            ['name' => 'Hi-Vis Clothing', 'desc' => 'Reflective vests, safety jackets', 'price' => 'From KES 1,800'],
            ['name' => 'Safety Footwear', 'desc' => 'Safety shoes, boots, gumboots', 'price' => 'From KES 3,500']
        ]
    ],
    'industrial' => [
        'title' => 'Industrial Tools & Machinery',
        'icon' => 'fa-tools',
        'description' => 'Professional-grade tools for heavy-duty work',
        'items' => [
            ['name' => 'Power Tools', 'desc' => 'Drills, saws, grinders, sanders', 'price' => 'From KES 8,000'],
            ['name' => 'Hand Tools', 'desc' => 'Wrenches, screwdrivers, pliers', 'price' => 'From KES 2,000'],
            ['name' => 'Measuring Equipment', 'desc' => 'Tape measures, levels, calipers', 'price' => 'From KES 1,500'],
            ['name' => 'Machinery', 'desc' => 'Generators, compressors, pumps', 'price' => 'From KES 50,000'],
            ['name' => 'Fasteners', 'desc' => 'Bolts, nuts, screws, nails', 'price' => 'From KES 300'],
            ['name' => 'Workshop Equipment', 'desc' => 'Workbenches, tool chests, stands', 'price' => 'From KES 10,000']
        ]
    ],
    'electronics' => [
        'title' => 'Electronics & Technology',
        'icon' => 'fa-microchip',
        'description' => 'Cutting-edge tech for modern businesses',
        'items' => [
            ['name' => 'IT Products', 'desc' => 'Computers, laptops, monitors', 'price' => 'From KES 35,000'],
            ['name' => 'Gadgets & Accessories', 'desc' => 'Smartphones, tablets, wearables', 'price' => 'From KES 15,000'],
            ['name' => 'Networking Equipment', 'desc' => 'Routers, switches, access points', 'price' => 'From KES 8,000'],
            ['name' => 'Storage Devices', 'desc' => 'Hard drives, SSDs, USB drives', 'price' => 'From KES 2,500'],
            ['name' => 'Audio Visual', 'desc' => 'Projectors, speakers, microphones', 'price' => 'From KES 12,000'],
            ['name' => 'Cables & Connectors', 'desc' => 'HDMI, USB, power cables', 'price' => 'From KES 500']
        ]
    ],
    'hospitality' => [
        'title' => 'Hospitality & Catering',
        'icon' => 'fa-utensils',
        'description' => 'Premium supplies for hospitality businesses',
        'items' => [
            ['name' => 'Foodservice Disposables', 'desc' => 'Plates, cups, cutlery, takeout containers', 'price' => 'From KES 800'],
            ['name' => 'Kitchenware', 'desc' => 'Cookware, bakeware, utensils', 'price' => 'From KES 3,000'],
            ['name' => 'Hotel Amenities', 'desc' => 'Toiletries, towels, bed linens', 'price' => 'From KES 2,500'],
            ['name' => 'Restaurant Supplies', 'desc' => 'Menu holders, table numbers, POS supplies', 'price' => 'From KES 1,500'],
            ['name' => 'Bar Equipment', 'desc' => 'Glassware, bar tools, dispensers', 'price' => 'From KES 5,000'],
            ['name' => 'Cleaning for Hospitality', 'desc' => 'Commercial-grade cleaning supplies', 'price' => 'From KES 2,000']
        ]
    ],
    'retail' => [
        'title' => 'Retail Solutions',
        'icon' => 'fa-store',
        'description' => 'Everything for retail store operations',
        'items' => [
            ['name' => 'Packaging Materials', 'desc' => 'Boxes, bags, wrapping paper', 'price' => 'From KES 500'],
            ['name' => 'Display Fixtures', 'desc' => 'Shelves, stands, racks', 'price' => 'From KES 8,000'],
            ['name' => 'POS Accessories', 'desc' => 'Receipt printers, cash drawers, scanners', 'price' => 'From KES 10,000'],
            ['name' => 'Pricing & Labels', 'desc' => 'Price tags, labels, label makers', 'price' => 'From KES 800'],
            ['name' => 'Shopping Bags', 'desc' => 'Paper bags, plastic bags, tote bags', 'price' => 'From KES 200'],
            ['name' => 'Security Equipment', 'desc' => 'Security tags, CCTV cameras', 'price' => 'From KES 15,000']
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse our extensive range of quality general supplies - Office supplies, cleaning products, safety equipment, industrial tools, and more.">
    <meta name="keywords" content="office supplies, cleaning products, safety equipment, industrial tools, electronics, Kenya, general suppliers">
    <meta name="author" content="Aluora General Suppliers Limited">
    <title>Products - Aluora General Suppliers Limited</title>
    
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
            <h1>Our Products</h1>
            <p>Quality supplies for every industry and business need</p>
            <div class="breadcrumb">
                <a href="index.php">Home</a>
                <span>/</span>
                <span>Products</span>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section" id="office">
        <div class="container">
            <div class="section-header">
                <span class="section-subtitle">What We Offer</span>
                <h2>Explore Our Product Categories</h2>
                <p>From office essentials to industrial equipment, we have everything you need</p>
            </div>
            
            <?php foreach ($products as $category_id => $category): ?>
            <div class="product-category-section" id="<?php echo $category_id; ?>">
                <div class="category-header">
                    <div class="category-icon">
                        <i class="fas <?php echo $category['icon']; ?>"></i>
                    </div>
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($category['title']); ?></h3>
                        <p><?php echo htmlspecialchars($category['description']); ?></p>
                    </div>
                </div>
                
                <div class="products-grid">
                    <?php if (empty($category['items'])): ?>
                    <p class="no-products">No products available in this category yet.</p>
                    <?php else: ?>
                    <?php foreach ($category['items'] as $item): ?>
                    <?php 
                        // Handle both old format (array) and new format (object-like)
                        $item_name = isset($item['name']) ? $item['name'] : ($item['title'] ?? 'Product');
                        $item_desc = isset($item['description']) ? $item['description'] : ($item['short_description'] ?? '');
                        $item_price = isset($item['price']) ? 'KES ' . number_format($item['price'], 2) : 'Contact for price';
                        $item_image = !empty($item['images']) ? htmlspecialchars($item['images']) : 'https://images.unsplash.com/photo-1586495777744-4413f21062fa?w=400';
                        $item_stock = $item['stock_quantity'] ?? 0;
                    ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($item_stock > 0): ?>
                            <div class="product-badge">In Stock</div>
                            <?php else: ?>
                            <div class="product-badge out-of-stock">Out of Stock</div>
                            <?php endif; ?>
                            <img src="<?php echo $item_image; ?>" alt="<?php echo htmlspecialchars($item_name); ?>">
                        </div>
                        <div class="product-info">
                            <span class="product-category"><?php echo htmlspecialchars($category['title']); ?></span>
                            <h3><?php echo htmlspecialchars($item_name); ?></h3>
                            <p><?php echo htmlspecialchars($item_desc); ?></p>
                            <div class="product-meta">
                                <span class="product-price"><?php echo $item_price; ?></span>
                                <a href="contact.php" class="product-btn">Request Quote</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($products)): ?>
            <div class="no-products-message">
                <i class="fas fa-box-open"></i>
                <h3>No Products Available</h3>
                <p>We currently have no products listed. Please check back later or contact us for custom orders.</p>
                <a href="contact.php" class="btn btn-primary">Contact Us</a>
            </div>
            <?php endif; ?>
            
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="why-choose-us">
        <div class="container">
            <div class="why-choose-content">
                <div class="why-choose-image">
                    <div class="image-stack">
                        <div class="image-main">
                            <img src="https://images.unsplash.com/photo-1553413077-190dd305871c?w=500" alt="Quality Products">
                        </div>
                        <div class="image-floating">
                            <div class="floating-card">
                                <i class="fas fa-check-circle"></i>
                                <span>Quality Guaranteed</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="why-choose-text">
                    <span class="section-subtitle">Why Choose Us</span>
                    <h2>The Aluora GSL Advantage</h2>
                    <p>When you choose Aluora General Suppliers Limited, you're choosing quality, reliability, and exceptional service.</p>
                    
                    <div class="features-list">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-shipping-fast"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Fast Delivery</h4>
                                <p>Quick and reliable delivery across Kenya and East Africa.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Quality Assured</h4>
                                <p>All products undergo rigorous quality checks before delivery.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-headset"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Expert Support</h4>
                                <p>Our knowledgeable team is ready to help you find the right products.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-undo"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Easy Returns</h4>
                                <p>Hassle-free return policy for defective or incorrect items.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Can't Find What You're Looking For?</h2>
                <p>Contact us with your specific requirements and we'll source it for you.</p>
                <div class="cta-buttons">
                    <a href="contact.php" class="btn btn-primary btn-large">Contact Us</a>
                    <a href="tel:+254715173207" class="btn btn-outline-white btn-large">
                        <i class="fas fa-phone"></i> Call Now
                    </a>
                </div>
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