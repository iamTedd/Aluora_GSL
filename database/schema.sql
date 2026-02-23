-- =============================================================================
-- ALUORA GENERAL SUPPLIERS LIMITED (aluoragsl.com)
-- DATABASE SCHEMA
-- =============================================================================
-- This is the complete database schema for the Aluora GSL website
-- Run this SQL to create all required tables
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Table: users
-- Description: Stores all registered users (customers and admins)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `company` VARCHAR(255) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `country` VARCHAR(100) DEFAULT 'Kenya',
    `role` ENUM('customer', 'admin', 'staff', 'manager', 'vendor', 'accountant', 'delivery_person') NOT NULL DEFAULT 'customer',
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `email_verified` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `last_login` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_email` (`email`),
    KEY `idx_role` (`role`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Table: categories
-- Description: Product categories for the catalog
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `parent_id` INT(11) DEFAULT NULL,
    `sort_order` INT(11) DEFAULT 0,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_parent` (`parent_id`),
    KEY `idx_slug` (`slug`),
    FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Table: products
-- Description: Products catalog
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `sku` VARCHAR(100) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `short_description` VARCHAR(500) DEFAULT NULL,
    `category_id` INT(11) NOT NULL,
    `brand` VARCHAR(255) DEFAULT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `sale_price` DECIMAL(10,2) DEFAULT NULL,
    `stock_quantity` INT(11) DEFAULT 0,
    `unit` VARCHAR(50) DEFAULT 'piece',
    `min_order_quantity` INT(11) DEFAULT 1,
    `images` TEXT DEFAULT NULL,
    `specifications` TEXT DEFAULT NULL,
    `featured` TINYINT(1) DEFAULT 0,
    `status` ENUM('active', 'inactive', 'out_of_stock') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_category` (`category_id`),
    KEY `idx_sku` (`sku`),
    KEY `idx_slug` (`slug`),
    KEY `idx_featured` (`featured`),
    KEY `idx_status` (`status`),
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Table: orders
-- Description: Customer orders
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_number` VARCHAR(50) NOT NULL UNIQUE,
    `user_id` INT(11) NOT NULL,
    `subtotal` DECIMAL(10,2) NOT NULL,
    `tax` DECIMAL(10,2) DEFAULT 0.00,
    `shipping` DECIMAL(10,2) DEFAULT 0.00,
    `total` DECIMAL(10,2) NOT NULL,
    `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    `payment_status` ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    `payment_method` VARCHAR(100) DEFAULT NULL,
    `shipping_address` TEXT DEFAULT NULL,
    `billing_address` TEXT DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_order_number` (`order_number`),
    KEY `idx_user` (`user_id`),
    KEY `idx_status` (`status`),
    KEY `idx_created` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Table: order_items
-- Description: Individual items in an order
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL,
    `product_id` INT(11) NOT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `sku` VARCHAR(100) DEFAULT NULL,
    `quantity` INT(11) NOT NULL,
    `unit_price` DECIMAL(10,2) NOT NULL,
    `total` DECIMAL(10,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_order` (`order_id`),
    KEY `idx_product` (`product_id`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Table: tenders
-- Description: Customer tender requests
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tenders` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `tender_number` VARCHAR(50) NOT NULL UNIQUE,
    `user_id` INT(11) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `category` VARCHAR(255) DEFAULT NULL,
    `quantity` VARCHAR(100) DEFAULT NULL,
    `budget_range` VARCHAR(100) DEFAULT NULL,
    `deadline` DATE DEFAULT NULL,
    `status` ENUM('pending', 'reviewed', 'quoted', 'accepted', 'rejected', 'closed') NOT NULL DEFAULT 'pending',
    `admin_notes` TEXT DEFAULT NULL,
    `quote_amount` DECIMAL(10,2) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_tender_number` (`tender_number`),
    KEY `idx_user` (`user_id`),
    KEY `idx_status` (`status`),
    KEY `idx_created` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Table: support_tickets
-- Description: Customer support tickets
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `support_tickets` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `ticket_number` VARCHAR(50) NOT NULL UNIQUE,
    `user_id` INT(11) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `category` ENUM('general', 'order', 'product', 'payment', 'technical', 'other') NOT NULL DEFAULT 'general',
    `priority` ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
    `status` ENUM('open', 'in_progress', 'waiting_customer', 'resolved', 'closed') NOT NULL DEFAULT 'open',
    `assigned_to` INT(11) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `resolved_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_ticket_number` (`ticket_number`),
    KEY `idx_user` (`user_id`),
    KEY `idx_status` (`status`),
    KEY `idx_priority` (`priority`),
    KEY `idx_assigned` (`assigned_to`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Table: ticket_messages
-- Description: Messages in support tickets
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ticket_messages` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `ticket_id` INT(11) NOT NULL,
    `user_id` INT(11) NOT NULL,
    `message` TEXT NOT NULL,
    `is_internal` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ticket` (`ticket_id`),
    KEY `idx_user` (`user_id`),
    FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Table: chat_messages
-- Description: AI chatbot conversation history
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `chat_messages` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `session_id` VARCHAR(100) DEFAULT NULL,
    `user_id` INT(11) DEFAULT NULL,
    `message` TEXT NOT NULL,
    `response` TEXT DEFAULT NULL,
    `is_from_ai` TINYINT(1) DEFAULT 1,
    `rating` TINYINT(1) DEFAULT NULL,
    `rating_comment` TEXT DEFAULT NULL,
    `human_requested` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_session` (`session_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_created` (`created_at`),
    KEY `idx_rating` (`rating`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Table: human_chat_requests
-- Description: Requests to chat with human support
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `human_chat_requests` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `chat_message_id` INT(11) DEFAULT NULL,
    `subject` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    `assigned_to` INT(11) DEFAULT NULL,
    `started_at` DATETIME DEFAULT NULL,
    `ended_at` DATETIME DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_status` (`status`),
    KEY `idx_assigned` (`assigned_to`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Table: activity_log
-- Description: User activity tracking
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `activity_log` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_created` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Table: notifications
-- Description: User notifications
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('info', 'success', 'warning', 'error', 'order', 'tender', 'ticket') NOT NULL DEFAULT 'info',
    `link` VARCHAR(255) DEFAULT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_read` (`is_read`),
    KEY `idx_created` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Table: product_reviews
-- Description: Product reviews and ratings
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_reviews` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `product_id` INT(11) NOT NULL,
    `user_id` INT(11) NOT NULL,
    `rating` TINYINT(1) NOT NULL,
    `title` VARCHAR(255) DEFAULT NULL,
    `review` TEXT DEFAULT NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_product` (`product_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_status` (`status`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Table: newsletter_subscribers
-- Description: Newsletter subscription list
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `name` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('active', 'unsubscribed') NOT NULL DEFAULT 'active',
    `subscribed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `unsubscribed_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_email` (`email`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- SAMPLE DATA - Insert default admin and categories
-- -----------------------------------------------------------------------------

-- Insert default admin user (password: admin123)
INSERT INTO `users` (`email`, `password`, `first_name`, `last_name`, `phone`, `role`, `status`, `email_verified`) VALUES
('admin@aluoragsl.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', '+254715173207', 'admin', 'active', 1)
ON DUPLICATE KEY UPDATE `email` = `email`;

-- Insert sample categories
INSERT INTO `categories` (`name`, `slug`, `description`, `sort_order`, `status`) VALUES
('Office Supplies', 'office-supplies', 'Everything for your office needs', 1, 'active'),
('Cleaning & Janitorial', 'cleaning-janitorial', 'Cleaning supplies and equipment', 2, 'active'),
('Safety Equipment', 'safety-equipment', 'PPE and safety gear', 3, 'active'),
('Industrial Tools', 'industrial-tools', 'Tools and machinery', 4, 'active'),
('Electronics & Technology', 'electronics-technology', 'IT products and gadgets', 5, 'active'),
('Hospitality & Catering', 'hospitality-catering', 'Foodservice and hotel supplies', 6, 'active'),
('Retail Solutions', 'retail-solutions', 'Retail displays and packaging', 7, 'active')
ON DUPLICATE KEY UPDATE `name` = `name`;

-- Insert sample products
INSERT INTO `products` (`sku`, `name`, `slug`, `description`, `category_id`, `price`, `stock_quantity`, `featured`, `status`) VALUES
('OFF-001', 'A4 Paper Ream (500 sheets)', 'a4-paper-ream', 'High quality A4 paper, 80gsm, 500 sheets per ream', 1, 450.00, 1000, 1, 'active'),
('OFF-002', 'Ballpoint Pens (Box of 50)', 'ballpoint-pens-box', 'Blue ballpoint pens, medium point, box of 50', 1, 350.00, 500, 1, 'active'),
('SAF-001', 'Safety Helmet (White)', 'safety-helmet-white', 'Industrial safety helmet, white, adjustable', 3, 850.00, 200, 1, 'active'),
('SAF-002', 'Safety Gloves (Pack of 10)', 'safety-gloves-pack', 'Reusable safety gloves, pack of 10 pairs', 3, 650.00, 300, 0, 'active'),
('CLN-001', 'Industrial Floor Cleaner (5L)', 'industrial-floor-cleaner', 'Concentrated floor cleaner, 5 liter container', 2, 1200.00, 150, 1, 'active'),
('CLN-002', 'Hand Sanitizer (500ml)', 'hand-sanitizer-500ml', 'Antibacterial hand sanitizer, 500ml with pump', 2, 280.00, 800, 1, 'active'),
('ELC-001', 'Wireless Mouse', 'wireless-mouse', 'USB wireless mouse, ergonomic design', 5, 1200.00, 250, 1, 'active'),
('ELC-002', 'USB Flash Drive 32GB', 'usb-flash-drive-32gb', '32GB USB 3.0 flash drive', 5, 650.00, 400, 0, 'active')
ON DUPLICATE KEY UPDATE `sku` = `sku`;

-- Insert newsletter sample
INSERT INTO `newsletter_subscribers` (`email`, `name`, `status`) VALUES
('aluoragsl@gmail.com', 'Aluora GSL', 'active')
ON DUPLICATE KEY UPDATE `email` = `email`;

-- =============================================================================
-- END OF DATABASE SCHEMA
-- =============================================================================
