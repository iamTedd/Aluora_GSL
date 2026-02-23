<?php
/**
 * Aluora GSL - Database Configuration & Functions
 * Complete System Configuration
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'aluora_gsldb');

// Application Configuration
define('APP_NAME', 'Aluora General Suppliers Limited');
define('APP_URL', 'http://localhost/Aluora_GSL');
define('ADMIN_URL', APP_URL . '/admin');
define('APP_EMAIL', 'aluoragsl@gmail.com');
define('APP_PHONE', '+254-715-173-207');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_start();

// Error Reporting (Production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Timezone
date_default_timezone_set('Africa/Nairobi');

// Database Connection
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        return null;
    }
}

// Create Database and Tables
function initializeDatabase() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        $pdo->exec("USE " . DB_NAME);
        
        // Users Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            phone VARCHAR(20) NOT NULL,
            company VARCHAR(100),
            password VARCHAR(255) NOT NULL,
            role ENUM('customer', 'admin', 'staff') DEFAULT 'customer',
            status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
            email_verified TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            INDEX idx_email (email),
            INDEX idx_role (role)
        )");
        
        // Categories Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            icon VARCHAR(50),
            image VARCHAR(255),
            parent_id INT NULL,
            sort_order INT DEFAULT 0,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
        )");
        
        // Products Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category_id INT NOT NULL,
            name VARCHAR(200) NOT NULL,
            slug VARCHAR(200) NOT NULL UNIQUE,
            description TEXT,
            short_description VARCHAR(500),
            price DECIMAL(12,2) NOT NULL,
            cost_price DECIMAL(12,2),
            sku VARCHAR(50) UNIQUE,
            stock_quantity INT DEFAULT 0,
            low_stock_threshold INT DEFAULT 10,
            unit VARCHAR(20) DEFAULT 'piece',
            image VARCHAR(255),
            images JSON,
            specifications JSON,
            featured TINYINT(1) DEFAULT 0,
            status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
            views INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
            INDEX idx_category (category_id),
            INDEX idx_status (status),
            INDEX idx_featured (featured)
        )");
        
        // Orders Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_number VARCHAR(50) NOT NULL UNIQUE,
            user_id INT NOT NULL,
            status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
            subtotal DECIMAL(12,2) NOT NULL,
            tax DECIMAL(12,2) DEFAULT 0,
            shipping_cost DECIMAL(12,2) DEFAULT 0,
            total DECIMAL(12,2) NOT NULL,
            payment_method VARCHAR(50),
            payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
            shipping_name VARCHAR(100),
            shipping_phone VARCHAR(20),
            shipping_address TEXT,
            shipping_city VARCHAR(100),
            shipping_county VARCHAR(100),
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user (user_id),
            INDEX idx_status (status),
            INDEX idx_order_number (order_number)
        )");
        
        // Order Items Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            product_name VARCHAR(200) NOT NULL,
            product_price DECIMAL(12,2) NOT NULL,
            quantity INT NOT NULL,
            subtotal DECIMAL(12,2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
        )");
        
        // Tenders Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS tenders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tender_number VARCHAR(50) NOT NULL UNIQUE,
            user_id INT NOT NULL,
            title VARCHAR(200) NOT NULL,
            description TEXT NOT NULL,
            category VARCHAR(100),
            quantity VARCHAR(100),
            budget_range VARCHAR(100),
            deadline DATE NOT NULL,
            documents JSON,
            status ENUM('pending', 'reviewing', 'quoted', 'accepted', 'rejected', 'expired') DEFAULT 'pending',
            admin_notes TEXT,
            quoted_price DECIMAL(12,2),
            assigned_staff_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (assigned_staff_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_user (user_id),
            INDEX idx_status (status)
        )");
        
        // Quotations Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS quotations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            quotation_number VARCHAR(50) NOT NULL UNIQUE,
            user_id INT NOT NULL,
            order_id INT,
            tender_id INT,
            items JSON NOT NULL,
            subtotal DECIMAL(12,2) NOT NULL,
            tax DECIMAL(12,2) DEFAULT 0,
            total DECIMAL(12,2) NOT NULL,
            valid_until DATE NOT NULL,
            notes TEXT,
            status ENUM('draft', 'sent', 'accepted', 'rejected', 'expired') DEFAULT 'draft',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
            FOREIGN KEY (tender_id) REFERENCES tenders(id) ON DELETE SET NULL
        )");
        
        // Chat Messages Table (AI + Human Support)
        $pdo->exec("CREATE TABLE IF NOT EXISTS chat_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(100) NOT NULL,
            user_id INT,
            message TEXT NOT NULL,
            response TEXT,
            is_from_ai TINYINT(1) DEFAULT 1,
            is_from_human TINYINT(1) DEFAULT 0,
            rating INT,
            feedback TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_session (session_id),
            INDEX idx_user (user_id)
        )");
        
        // Support Tickets Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS support_tickets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ticket_number VARCHAR(50) NOT NULL UNIQUE,
            user_id INT NOT NULL,
            order_id INT,
            subject VARCHAR(200) NOT NULL,
            category ENUM('general', 'order', 'product', 'payment', 'technical', 'other') DEFAULT 'general',
            priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
            status ENUM('open', 'in_progress', 'waiting_customer', 'resolved', 'closed') DEFAULT 'open',
            messages JSON,
            assigned_to INT,
            resolved_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
            FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_user (user_id),
            INDEX idx_status (status)
        )");
        
        // Product Reviews Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            order_id INT,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            title VARCHAR(200),
            comment TEXT,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            helpful_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
            INDEX idx_product (product_id),
            INDEX idx_status (status)
        )");
        
        // Newsletter Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS newsletters (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) NOT NULL UNIQUE,
            name VARCHAR(100),
            subscribed TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Activity Logs Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(100) NOT NULL,
            description TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_user (user_id),
            INDEX idx_action (action)
        )");
        
        // Notifications Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            type VARCHAR(50) NOT NULL,
            title VARCHAR(200) NOT NULL,
            message TEXT,
            link VARCHAR(255),
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user (user_id),
            INDEX idx_read (is_read)
        )");
        
        // Settings Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Insert default admin user (password: admin123)
        $adminExists = $pdo->query("SELECT COUNT(*) FROM users WHERE email = 'admin@aluoragsl.com'")->fetchColumn();
        if (!$adminExists) {
            $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $pdo->exec("INSERT INTO users (first_name, last_name, email, phone, password, role, status, email_verified) 
                       VALUES ('Admin', 'User', 'admin@aluoragsl.com', '+254715173207', '$hashedPassword', 'admin', 'active', 1)");
        }
        
        // Insert default categories
        $categoriesExist = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        if (!$categoriesExist) {
            $pdo->exec("INSERT INTO categories (name, slug, description, icon, sort_order) VALUES
                       ('Office Supplies', 'office-supplies', 'Everything your workspace needs', 'fa-briefcase', 1),
                       ('Cleaning & Janitorial', 'cleaning-janitorial', 'Keep your spaces clean', 'fa-sparkles', 2),
                       ('Safety Equipment', 'safety-equipment', 'Protect your workforce', 'fa-shield-halved', 3),
                       ('Industrial Tools', 'industrial-tools', 'Professional-grade tools', 'fa-tools', 4),
                       ('Electronics & Tech', 'electronics-tech', 'Cutting-edge technology', 'fa-microchip', 5),
                       ('Hospitality & Catering', 'hospitality-catering', 'Premium hospitality supplies', 'fa-utensils', 6),
                       ('Retail Solutions', 'retail-solutions', 'Everything for retail', 'fa-store', 7)");
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Database Initialization Error: " . $e->getMessage());
        return false;
    }
}

// Helper Functions
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function generateUniqueID($prefix = '') {
    return $prefix . strtoupper(uniqid() . bin2hex(random_bytes(4)));
}

function generateOrderNumber() {
    return 'GSL-' . date('Ymd') . '-' . strtoupper(uniqid());
}

function generateTenderNumber() {
    return 'TND-' . date('Ymd') . '-' . strtoupper(uniqid());
}

function generateTicketNumber() {
    return 'TKT-' . date('Ymd') . '-' . strtoupper(uniqid());
}

function formatCurrency($amount) {
    return 'KES ' . number_format($amount, 2);
}

function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff/60) . ' minutes ago';
    if ($diff < 86400) return floor($diff/3600) . ' hours ago';
    if ($diff < 604800) return floor($diff/86400) . ' days ago';
    return date('M d, Y', $time);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isStaff() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'staff', 'manager']);
}

// Role-based access functions
function isManager() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'manager';
}

function isVendor() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'vendor';
}

function isAccountant() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'accountant';
}

function isDeliveryPerson() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'delivery_person';
}

function isCustomer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'customer';
}

// Get dashboard URL based on role
function getDashboardUrl() {
    if (!isLoggedIn()) {
        return 'index.php';
    }
    
    switch ($_SESSION['role']) {
        case 'admin':
            return 'admin/index.php';
        case 'manager':
            return 'manager/index.php';
        case 'vendor':
            return 'vendor/index.php';
        case 'accountant':
            return 'accountant/index.php';
        case 'delivery_person':
            return 'delivery/index.php';
        case 'staff':
            return 'admin/index.php';
        case 'customer':
        default:
            return 'dashboard.php';
    }
}

// Require role - redirect if not authorized
function requireRole($roles) {
    if (!isLoggedIn()) {
        redirect('index.php');
    }
    
    $roles = is_array($roles) ? $roles : [$roles];
    
    if (!in_array($_SESSION['role'], $roles)) {
        redirect(getDashboardUrl());
    }
}

// Require login - redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('index.php');
    }
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function logActivity($userId, $action, $description = '') {
    try {
        $pdo = getDBConnection();
        if ($pdo) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $action, $description, $ip, $userAgent]);
        }
    } catch (Exception $e) {
        error_log("Log Activity Error: " . $e->getMessage());
    }
}

function createNotification($userId, $type, $title, $message, $link = '') {
    try {
        $pdo = getDBConnection();
        if ($pdo) {
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $type, $title, $message, $link]);
        }
    } catch (Exception $e) {
        error_log("Notification Error: " . $e->getMessage());
    }
}

function getNotificationCount($userId) {
    try {
        $pdo = getDBConnection();
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$userId]);
            return $stmt->fetchColumn();
        }
    } catch (Exception $e) {
        return 0;
    }
    return 0;
}

// AI Response Function
function getAIResponse($message) {
    $message = strtolower(trim($message));
    
    $responses = [
        // Greetings
        'hello' => "Hello! Welcome to Aluora General Suppliers Limited. How can I assist you today? I can help you with product information, placing orders, submitting tenders, or connecting you with a human support agent.",
        'hi' => "Hi there! I'm here to help you find what you need. What can I assist you with today?",
        'hey' => "Hey! Welcome to Aluora GSL. How may I help you?",
        
        // Products
        'product' => "We offer a wide range of products including:\n\n• Office Supplies (Stationery, furniture, equipment)\n• Cleaning & Janitorial Supplies\n• Safety Equipment (PPE, first aid)\n• Industrial Tools & Machinery\n• Electronics & Technology\n• Hospitality & Catering Supplies\n• Retail Solutions\n\nWould you like to browse our products or need more details about a specific category?",
        
        'price' => "Our prices vary depending on the product. For detailed pricing, please:\n1. Browse our products page\n2. Request a quote for bulk orders\n3. Contact us directly at +254-715-173-207\n\nWe offer competitive pricing with discounts for bulk orders!",
        
        'order' => "To place an order, you can:\n\n1. Browse products and add to cart\n2. Call us at +254-715-173-207\n3. Email us at aluoragsl@gmail.com\n4. Use our online order form\n\nWould you like me to help you with anything specific about ordering?",
        
        // Tenders
        'tender' => "We welcome tender submissions! To submit a tender:\n\n1. Register an account or login\n2. Go to 'Submit Tender' in your dashboard\n3. Fill in the required details\n4. Upload necessary documents\n\nOur team will review and get back to you within 48 hours.",
        
        // Contact
        'contact' => "You can reach us through:\n\n📞 Phone: +254-715-173-207\n📧 Email: aluoragsl@gmail.com\n🌐 Website: www.aluoragsl.com\n\nOur business hours:\nMon-Fri: 8:00 AM - 6:00 PM\nSat: 9:00 AM - 2:00 PM",
        
        // Delivery
        'delivery' => "We offer delivery services across Kenya and East Africa! Delivery times and costs vary based on location. Contact us for specific delivery information.",
        
        'shipping' => "We provide reliable shipping services. Delivery times typically range from 1-5 business days within Kenya. International shipping is also available.",
        
        // Payment
        'payment' => "We accept various payment methods:\n\n• Bank Transfer\n• M-Pesa\n• Cash on Delivery\n• Cheque\n\nContact us for more details on payment options.",
        
        // Returns
        'returns' => "Our return policy allows returns within 7 days of delivery for defective or incorrect items. Please contact our support team to initiate a return.",
        
        // Support
        'help' => "I'm here to help! I can assist with:\n\n📦 Order placement and tracking\n💼 Tender submissions\n📞 Contact information\n📋 Product inquiries\n🔧 Technical support\n\nFor immediate human assistance, say 'talk to human' or 'connect to agent'.",
        
        // Default
        'default' => "Thank you for contacting Aluora GSL! I'm here to help. Could you please provide more details about what you need? If you'd prefer to speak with a human agent, just say 'talk to human' and I'll connect you with one of our team members."
    ];
    
    // Check for keyword matches
    foreach ($responses as $keyword => $response) {
        if (strpos($message, $keyword) !== false) {
            return $response;
        }
    }
    
    return $responses['default'];
}

// Initialize database on first run
if (!isset($_SESSION['db_initialized'])) {
    initializeDatabase();
    $_SESSION['db_initialized'] = true;
}
