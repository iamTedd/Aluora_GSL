-- =============================================================================
-- ALUORA GSL - PAYMENTS & NOTIFICATIONS EXTENSION
-- Additional tables for Payment Gateway, SMS/Email Notifications
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Table: payment_methods
-- Description: Available payment methods (M-Pesa, Airtel, Banks)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payment_methods` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `type` ENUM('mobile_money', 'bank', 'card') NOT NULL,
    `logo` VARCHAR(255) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT(11) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_slug` (`slug`),
    KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert payment methods
INSERT INTO `payment_methods` (`name`, `slug`, `type`, `logo`, `description`, `sort_order`) VALUES
('M-Pesa', 'mpesa', 'mobile_money', 'images/payments/mpesa.png', 'Pay via M-Pesa STK Push', 1),
('Airtel Money', 'airtel', 'mobile_money', 'images/payments/airtel.png', 'Pay via Airtel Money', 2),
('Equity Bank', 'equity', 'bank', 'images/payments/equity.png', 'Pay via Equity Bank', 3),
('KCB Bank', 'kcb', 'bank', 'images/payments/kcb.png', 'Pay via KCB Bank', 4),
('Co-operative Bank', 'cooperative', 'bank', 'images/payments/cooperative.png', 'Pay via Co-operative Bank', 5),
('Standard Chartered', 'stanchart', 'bank', 'images/payments/stanchart.png', 'Pay via Standard Chartered', 6),
('Visa/MasterCard', 'card', 'card', 'images/payments/visa.png', 'Pay via Credit/Debit Card', 7)
ON DUPLICATE KEY UPDATE `name` = `name`;

-- -----------------------------------------------------------------------------
-- Table: transactions
-- Description: All payment transactions
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `transactions` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `transaction_id` VARCHAR(100) NOT NULL UNIQUE,
    `user_id` INT(11) NOT NULL,
    `type` ENUM('order', 'tender', 'deposit', 'delivery') NOT NULL,
    `reference_id` INT(11) DEFAULT NULL,
    `amount` DECIMAL(12,2) NOT NULL,
    `currency` VARCHAR(10) DEFAULT 'KES',
    `payment_method` VARCHAR(50) NOT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `account_number` VARCHAR(100) DEFAULT NULL,
    `status` ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded') NOT NULL DEFAULT 'pending',
    `provider_reference` VARCHAR(255) DEFAULT NULL,
    `provider_response` TEXT DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `completed_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_transaction_id` (`transaction_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_type` (`type`),
    KEY `idx_reference` (`reference_id`),
    KEY `idx_status` (`status`),
    KEY `idx_method` (`payment_method`),
    KEY `idx_created` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Table: delivery_zones
-- Description: Delivery zones with fees
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_zones` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `base_fee` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `free_shipping_threshold` DECIMAL(10,2) DEFAULT NULL,
    `estimated_days` VARCHAR(50) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_slug` (`slug`),
    KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert delivery zones
INSERT INTO `delivery_zones` (`name`, `slug`, `description`, `base_fee`, `free_shipping_threshold`, `estimated_days`) VALUES
('Nairobi CBD', 'nairobi-cbd', 'Nairobi Central Business District', 0, 10000, 'Same Day'),
('Nairobi suburbs', 'nairobi-suburbs', 'Areas within Nairobi but outside CBD', 300, 15000, '1-2 Days'),
('Kiambu County', 'kiambu', 'Kiambu County areas', 500, 20000, '1-2 Days'),
('Kajiado County', 'kajiado', 'Kajiado County areas', 800, 25000, '2-3 Days'),
('Nakuru County', 'nakuru', 'Nakuru and surrounding', 1200, 30000, '2-3 Days'),
('Mombasa County', 'mombasa', 'Mombasa and coastal', 2000, 50000, '3-5 Days'),
('Other Counties', 'other', 'Rest of Kenya', 2500, 75000, '5-7 Days')
ON DUPLICATE KEY UPDATE `name` = `name`;

-- -----------------------------------------------------------------------------
-- Table: sms_log
-- Description: SMS notification log
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sms_log` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) DEFAULT NULL,
    `phone` VARCHAR(50) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('transaction', 'order', 'tender', 'verification', 'alert', 'general') NOT NULL,
    `reference_id` INT(11) DEFAULT NULL,
    `status` ENUM('pending', 'sent', 'delivered', 'failed') NOT NULL DEFAULT 'pending',
    `provider` VARCHAR(50) DEFAULT NULL,
    `provider_message_id` VARCHAR(255) DEFAULT NULL,
    `cost` DECIMAL(10,2) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_phone` (`phone`),
    KEY `idx_type` (`type`),
    KEY `idx_status` (`status`),
    KEY `idx_created` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Table: email_log
-- Description: Email notification log
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_log` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) DEFAULT NULL,
    `email` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `body` TEXT NOT NULL,
    `type` ENUM('transaction', 'order', 'tender', 'verification', 'alert', 'general') NOT NULL,
    `reference_id` INT(11) DEFAULT NULL,
    `status` ENUM('pending', 'sent', 'delivered', 'opened', 'failed') NOT NULL DEFAULT 'pending',
    `provider` VARCHAR(50) DEFAULT NULL,
    `provider_message_id` VARCHAR(255) DEFAULT NULL,
    `error_message` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `sent_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_email` (`email`),
    KEY `idx_type` (`type`),
    KEY `idx_status` (`status`),
    KEY `idx_created` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Table: admin_alerts
-- Description: Admin notification/alert system
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_alerts` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `type` ENUM('order', 'payment', 'tender', 'user', 'system', 'warning') NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `priority` ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
    `reference_type` VARCHAR(50) DEFAULT NULL,
    `reference_id` INT(11) DEFAULT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `is_dismissed` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_type` (`type`),
    KEY `idx_priority` (`priority`),
    KEY `idx_read` (`is_read`),
    KEY `idx_dismissed` (`is_dismissed`),
    KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Table: payment_settings
-- Description: Payment gateway configuration
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payment_settings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT DEFAULT NULL,
    `setting_group` VARCHAR(50) DEFAULT 'general',
    `description` VARCHAR(255) DEFAULT NULL,
    `is_encrypted` TINYINT(1) DEFAULT 0,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_key` (`setting_key`),
    KEY `idx_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default payment settings
INSERT INTO `payment_settings` (`setting_key`, `setting_value`, `setting_group`, `description`) VALUES
('currency', 'KES', 'general', 'Default currency code'),
('currency_symbol', 'KSh', 'general', 'Currency symbol'),
('site_name', 'Aluora GSL', 'general', 'Site name for emails'),
('support_email', 'aluoragsl@gmail.com', 'general', 'Support email'),
('support_phone', '+254715173207', 'general', 'Support phone'),
('mpesa_shortcode', '', 'mpesa', 'M-Pesa shortcode'),
('mpesa_consumer_key', '', 'mpesa', 'M-Pesa consumer key'),
('mpesa_consumer_secret', '', 'mpesa', 'M-Pesa consumer secret'),
('mpesa_passkey', '', 'mpesa', 'M-Pesa passkey'),
('mpesa_environment', 'sandbox', 'mpesa', 'M-Pesa environment (sandbox/live)'),
('airtel_client_id', '', 'airtel', 'Airtel Money client ID'),
('airtel_client_secret', '', 'airtel', 'Airtel Money client secret'),
('airtel_environment', 'sandbox', 'airtel', 'Airtel environment'),
('email_provider', 'smtp', 'email', 'Email provider (smtp/sendmail/api)'),
('smtp_host', '', 'email', 'SMTP host'),
('smtp_port', '587', 'email', 'SMTP port'),
('smtp_username', '', 'email', 'SMTP username'),
('smtp_password', '', 'email', 'SMTP password'),
('smtp_from_email', 'noreply@aluoragsl.com', 'email', 'From email address'),
('smtp_from_name', 'Aluora GSL', 'email', 'From name'),
('sms_provider', '', 'sms', 'SMS provider (twilio/africastalking)'),
('sms_api_key', '', 'sms', 'SMS API key'),
('sms_api_secret', '', 'sms', 'SMS API secret'),
('sms_sender_id', 'ALUORA', 'sms', 'SMS sender ID'),
('admin_notifications', '1', 'notifications', 'Send admin notifications'),
('order_notifications', '1', 'notifications', 'Send order notifications'),
('payment_notifications', '1', 'notifications', 'Send payment notifications'),
('tender_notifications', '1', 'notifications', 'Send tender notifications')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

-- -----------------------------------------------------------------------------
-- Table: order_delivery
-- Description: Delivery details for orders
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_delivery` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL UNIQUE,
    `delivery_zone` VARCHAR(100) DEFAULT NULL,
    `delivery_address` TEXT DEFAULT NULL,
    `delivery_fee` DECIMAL(10,2) DEFAULT 0,
    `estimated_delivery` DATE DEFAULT NULL,
    `actual_delivery` DATETIME DEFAULT NULL,
    `delivery_status` ENUM('pending', 'processing', 'shipped', 'in_transit', 'delivered', 'failed') DEFAULT 'pending',
    `tracking_number` VARCHAR(100) DEFAULT NULL,
    `delivery_notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_order` (`order_id`),
    KEY `idx_status` (`delivery_status`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Table: tender_quotes
-- Description: Quotes for tenders
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tender_quotes` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `tender_id` INT(11) NOT NULL,
    `quoted_price` DECIMAL(12,2) NOT NULL,
    `valid_until` DATE NOT NULL,
    `notes` TEXT DEFAULT NULL,
    `terms` TEXT DEFAULT NULL,
    `status` ENUM('pending', 'accepted', 'rejected', 'expired') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_tender` (`tender_id`),
    KEY `idx_status` (`status`),
    FOREIGN KEY (`tender_id`) REFERENCES `tenders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================================================
-- END OF PAYMENTS & NOTIFICATIONS SCHEMA
-- =============================================================================
