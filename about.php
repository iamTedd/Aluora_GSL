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

// Get current page
$current_page = 'about';
?>
<!DOCTYPE html>
<html lang="en">
<he
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Learn about Aluora General Suppliers Limited - Your trusted partner for high-quality general supplies in Kenya and beyond.">
    <meta name="keywords" content="about us, company profile, general suppliers, Kenya, Aluora GSL">
    <meta name="author" content="Aluora General Suppliers Limited">
    <title>About Us - Aluora General Suppliers Limited</title>
    
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
            <h1>About Us</h1>
            <p>Discover who we are and what drives us to deliver excellence</p>
            <div class="breadcrumb">
                <a href="index.php">Home</a>
                <span>/</span>
                <span>About Us</span>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1560472355-536de3962603?w=600" alt="Aluora General Suppliers - Warehouse">
                </div>
                <div class="about-text">
                    <span class="section-subtitle">Who We Are</span>
                    <h2>Empowering Businesses with Quality Supplies</h2>
                    <p>Aluora General Suppliers Limited is a dynamic and innovative company dedicated to providing high-quality general supplies and solutions to meet the diverse needs of businesses and individuals. With a strong commitment to customer satisfaction, reliability, and efficiency, we have established ourselves as a trusted partner in the industry.</p>
                    <p>Our extensive range of products spans across multiple categories, ensuring that we can cater to the unique requirements of various sectors. From office essentials to industrial equipment, we are your one-stop solution for all general supply needs.</p>
                    <a href="products.php" class="btn btn-primary">Explore Our Products</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Vision & Mission -->
    <section class="values-section">
        <div class="container">
            <div class="section-header">
                <span class="section-subtitle">Our Purpose</span>
                <h2>Vision & Mission</h2>
                <p>Guiding principles that drive our every action</p>
            </div>
            
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-eye"></i>
                    <h3                    </div>
>Vision</h3>
                    <p>To become a leading player in the general supplies sector by consistently delivering superior products and services that enhance the operational efficiency and growth of our clients.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3>Mission</h3>
                    <p>Our mission is to be a one-stop solution for all general supplies needs, offering a comprehensive range of products that cater to various industries while maintaining the highest standards of quality, service, and professionalism.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-gem"></i>
                    </div>
                    <h3>Quality</h3>
                    <p>We prioritize quality in all aspects of our operations, ensuring that our products meet or exceed industry standards.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Customer-Centric</h3>
                    <p>Our customers are at the heart of everything we do. We strive to understand their needs and provide tailored solutions.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3>Integrity</h3>
                    <p>We uphold the highest ethical standards in our business practices, fostering trust and transparency with our clients, partners, and stakeholders.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Core Values -->
    <section class="about-section" style="background: var(--off-white);">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <span class="section-subtitle">What We Stand For</span>
                    <h2>Our Core Values</h2>
                    <p>These values form the foundation of everything we do at Aluora General Suppliers Limited. They guide our decisions, shape our culture, and define how we interact with our clients and partners.</p>
                    
                    <div class="features-list" style="margin-top: 30px;">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Innovation</h4>
                                <p>Embracing innovation allows us to adapt to the evolving needs of the market and provide cutting-edge solutions.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-hand-holding-heart"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Reliability</h4>
                                <p>Clients count on us to deliver on time and consistently, and we take pride in being a dependable partner.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Teamwork</h4>
                                <p>Our diverse team collaborates seamlessly to deliver exceptional service and results.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Sustainability</h4>
                                <p>We are committed to environmentally responsible practices, sourcing eco-friendly products whenever possible and minimizing our carbon footprint.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=600" alt="Our Core Values - Teamwork">
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-us">
        <div class="container">
            <div class="why-choose-content">
                <div class="why-choose-image">
                    <div class="image-stack">
                        <div class="image-main">
                            <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?w=500" alt="Why Choose Aluora GSL">
                        </div>
                        <div class="image-floating">
                            <div class="floating-card">
                                <i class="fas fa-check-circle"></i>
                                <span>ISO 9001 Certified</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="why-choose-text">
                    <span class="section-subtitle">Why Choose Us</span>
                    <h2>Experience the Aluora GSL Difference</h2>
                    <p>At Aluora General Suppliers Limited, we are committed to elevating your operational efficiency by providing top-notch general supplies. Partner with us to experience convenience, quality, and professionalism like never before.</p>
                    
                    <div class="features-list">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Competitive Pricing</h4>
                                <p>We strive to offer competitive prices without compromising on quality.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-boxes-stacked"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Diverse Product Range</h4>
                                <p>We offer a comprehensive selection of products to meet various needs under one roof.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-medal"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Quality Assurance</h4>
                                <p>All our products undergo rigorous quality checks to ensure durability and reliability.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-headset"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Customer Support</h4>
                                <p>Our dedicated customer support team is always ready to assist with inquiries, orders, and support.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-sliders"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Customized Solutions</h4>
                                <p>We understand that every client has unique requirements, and we offer personalized solutions accordingly.</p>
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
                <h2>Ready to Partner with Us?</h2>
                <p>Join hundreds of satisfied clients who trust Aluora General Suppliers Limited for their business needs.</p>
                <div class="cta-buttons">
                    <a href="contact.php" class="btn btn-primary btn-large">Get in Touch</a>
                    <a href="products.php" class="btn btn-outline-white btn-large">View Products</a>
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