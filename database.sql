-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 23, 2026 at 10:09 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aluora_gsldb`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_alerts`
--

CREATE TABLE `admin_alerts` (
  `id` int(11) NOT NULL,
  `type` enum('order','payment','tender','user','system','warning') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_dismissed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `image`, `parent_id`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'House hold', 'house-hold', 'All household requirements in your dorestep', 'fa-cup', NULL, NULL, 0, 'active', '2026-02-23 15:09:58', '2026-02-23 15:09:58'),
(12, 'Hardware', 'hardware', 'All hardwarw requirements just  click away', 'fa-house', NULL, NULL, 0, 'active', '2026-02-23 16:39:36', '2026-02-23 16:39:36');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `response` text DEFAULT NULL,
  `is_from_ai` tinyint(1) DEFAULT 1,
  `rating` tinyint(1) DEFAULT NULL,
  `rating_comment` text DEFAULT NULL,
  `human_requested` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `session_id`, `user_id`, `message`, `response`, `is_from_ai`, `rating`, `rating_comment`, `human_requested`, `created_at`) VALUES
(1, '22qtq65q409s0kdkv1i0l7km9b', 1, 'our products', 'We offer a wide range of products including:\n\n• Office Supplies (Stationery, furniture, equipment)\n• Cleaning & Janitorial Supplies\n• Safety Equipment (PPE, first aid)\n• Industrial Tools & Machinery\n• Electronics & Technology\n• Hospitality & Catering Supplies\n• Retail Solutions\n\nWould you like to browse our products or need more details about a specific category?', 1, NULL, NULL, 0, '2026-02-23 13:23:14'),
(2, '22qtq65q409s0kdkv1i0l7km9b', 1, 'thanks', 'Thank you for contacting Aluora GSL! I\'m here to help. Could you please provide more details about what you need? If you\'d prefer to speak with a human agent, just say \'talk to human\' and I\'ll connect you with one of our team members.', 1, NULL, NULL, 0, '2026-02-23 13:23:30');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_zones`
--

CREATE TABLE `delivery_zones` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `base_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `free_shipping_threshold` decimal(10,2) DEFAULT NULL,
  `estimated_days` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_zones`
--

INSERT INTO `delivery_zones` (`id`, `name`, `slug`, `description`, `base_fee`, `free_shipping_threshold`, `estimated_days`, `is_active`, `created_at`) VALUES
(1, 'Nairobi CBD', 'nairobi-cbd', 'Nairobi Central Business District', 0.00, 10000.00, 'Same Day', 1, '2026-02-23 13:33:39'),
(2, 'Nairobi suburbs', 'nairobi-suburbs', 'Areas within Nairobi but outside CBD', 300.00, 15000.00, '1-2 Days', 1, '2026-02-23 13:33:39'),
(3, 'Kiambu County', 'kiambu', 'Kiambu County areas', 500.00, 20000.00, '1-2 Days', 1, '2026-02-23 13:33:39'),
(4, 'Kajiado County', 'kajiado', 'Kajiado County areas', 800.00, 25000.00, '2-3 Days', 1, '2026-02-23 13:33:39'),
(5, 'Nakuru County', 'nakuru', 'Nakuru and surrounding', 1200.00, 30000.00, '2-3 Days', 1, '2026-02-23 13:33:39'),
(6, 'Mombasa County', 'mombasa', 'Mombasa and coastal', 2000.00, 50000.00, '3-5 Days', 1, '2026-02-23 13:33:39'),
(7, 'Other Counties', 'other', 'Rest of Kenya', 2500.00, 75000.00, '5-7 Days', 1, '2026-02-23 13:33:39');

-- --------------------------------------------------------

--
-- Table structure for table `email_log`
--

CREATE TABLE `email_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `type` enum('transaction','order','tender','verification','alert','general') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `status` enum('pending','sent','delivered','opened','failed') NOT NULL DEFAULT 'pending',
  `provider` varchar(50) DEFAULT NULL,
  `provider_message_id` varchar(255) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sent_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `human_chat_requests`
--

CREATE TABLE `human_chat_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `chat_message_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `status` enum('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `assigned_to` int(11) DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error','order','tender','ticket') NOT NULL DEFAULT 'info',
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','confirmed','processing','shipped','delivered','cancelled','refunded') DEFAULT 'pending',
  `subtotal` decimal(12,2) NOT NULL,
  `tax` decimal(12,2) DEFAULT 0.00,
  `shipping_cost` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `shipping_name` varchar(100) DEFAULT NULL,
  `shipping_phone` varchar(20) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_county` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_delivery`
--

CREATE TABLE `order_delivery` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `delivery_zone` varchar(100) DEFAULT NULL,
  `delivery_address` text DEFAULT NULL,
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `estimated_delivery` date DEFAULT NULL,
  `actual_delivery` datetime DEFAULT NULL,
  `delivery_status` enum('pending','processing','shipped','in_transit','delivered','failed') DEFAULT 'pending',
  `tracking_number` varchar(100) DEFAULT NULL,
  `delivery_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `type` enum('mobile_money','bank','card') NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `slug`, `type`, `logo`, `description`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'M-Pesa', 'mpesa', 'mobile_money', 'images/payments/mpesa.png', 'Pay via M-Pesa STK Push', 1, 1, '2026-02-23 13:33:39'),
(2, 'Airtel Money', 'airtel', 'mobile_money', 'images/payments/airtel.png', 'Pay via Airtel Money', 1, 2, '2026-02-23 13:33:39'),
(3, 'Equity Bank', 'equity', 'bank', 'images/payments/equity.png', 'Pay via Equity Bank', 1, 3, '2026-02-23 13:33:39'),
(4, 'KCB Bank', 'kcb', 'bank', 'images/payments/kcb.png', 'Pay via KCB Bank', 1, 4, '2026-02-23 13:33:39'),
(5, 'Co-operative Bank', 'cooperative', 'bank', 'images/payments/cooperative.png', 'Pay via Co-operative Bank', 1, 5, '2026-02-23 13:33:39'),
(6, 'Standard Chartered', 'stanchart', 'bank', 'images/payments/stanchart.png', 'Pay via Standard Chartered', 1, 6, '2026-02-23 13:33:39'),
(7, 'Visa/MasterCard', 'card', 'card', 'images/payments/visa.png', 'Pay via Credit/Debit Card', 1, 7, '2026-02-23 13:33:39');

-- --------------------------------------------------------

--
-- Table structure for table `payment_settings`
--

CREATE TABLE `payment_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `description` varchar(255) DEFAULT NULL,
  `is_encrypted` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_settings`
--

INSERT INTO `payment_settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `description`, `is_encrypted`, `updated_at`) VALUES
(1, 'currency', 'KES', 'general', 'Default currency code', 0, '2026-02-23 13:33:40'),
(2, 'currency_symbol', 'KSh', 'general', 'Currency symbol', 0, '2026-02-23 13:33:40'),
(3, 'site_name', 'Aluora GSL', 'general', 'Site name for emails', 0, '2026-02-23 13:33:40'),
(4, 'support_email', 'aluoragsl@gmail.com', 'general', 'Support email', 0, '2026-02-23 13:33:40'),
(5, 'support_phone', '+254715173207', 'general', 'Support phone', 0, '2026-02-23 13:33:40'),
(6, 'mpesa_shortcode', '', 'mpesa', 'M-Pesa shortcode', 0, '2026-02-23 13:33:40'),
(7, 'mpesa_consumer_key', '', 'mpesa', 'M-Pesa consumer key', 0, '2026-02-23 13:33:40'),
(8, 'mpesa_consumer_secret', '', 'mpesa', 'M-Pesa consumer secret', 0, '2026-02-23 13:33:40'),
(9, 'mpesa_passkey', '', 'mpesa', 'M-Pesa passkey', 0, '2026-02-23 13:33:40'),
(10, 'mpesa_environment', 'sandbox', 'mpesa', 'M-Pesa environment (sandbox/live)', 0, '2026-02-23 13:33:40'),
(11, 'airtel_client_id', '', 'airtel', 'Airtel Money client ID', 0, '2026-02-23 13:33:40'),
(12, 'airtel_client_secret', '', 'airtel', 'Airtel Money client secret', 0, '2026-02-23 13:33:40'),
(13, 'airtel_environment', 'sandbox', 'airtel', 'Airtel environment', 0, '2026-02-23 13:33:40'),
(14, 'email_provider', 'smtp', 'email', 'Email provider (smtp/sendmail/api)', 0, '2026-02-23 13:33:40'),
(15, 'smtp_host', '', 'email', 'SMTP host', 0, '2026-02-23 13:33:40'),
(16, 'smtp_port', '587', 'email', 'SMTP port', 0, '2026-02-23 13:33:40'),
(17, 'smtp_username', '', 'email', 'SMTP username', 0, '2026-02-23 13:33:40'),
(18, 'smtp_password', '', 'email', 'SMTP password', 0, '2026-02-23 13:33:40'),
(19, 'smtp_from_email', 'noreply@aluoragsl.com', 'email', 'From email address', 0, '2026-02-23 13:33:40'),
(20, 'smtp_from_name', 'Aluora GSL', 'email', 'From name', 0, '2026-02-23 13:33:40'),
(21, 'sms_provider', '', 'sms', 'SMS provider (twilio/africastalking)', 0, '2026-02-23 13:33:40'),
(22, 'sms_api_key', '', 'sms', 'SMS API key', 0, '2026-02-23 13:33:40'),
(23, 'sms_api_secret', '', 'sms', 'SMS API secret', 0, '2026-02-23 13:33:40'),
(24, 'sms_sender_id', 'ALUORA', 'sms', 'SMS sender ID', 0, '2026-02-23 13:33:40'),
(25, 'admin_notifications', '1', 'notifications', 'Send admin notifications', 0, '2026-02-23 13:33:40'),
(26, 'order_notifications', '1', 'notifications', 'Send order notifications', 0, '2026-02-23 13:33:40'),
(27, 'payment_notifications', '1', 'notifications', 'Send payment notifications', 0, '2026-02-23 13:33:40'),
(28, 'tender_notifications', '1', 'notifications', 'Send tender notifications', 0, '2026-02-23 13:33:40');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `cost_price` decimal(12,2) DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 10,
  `unit` varchar(20) DEFAULT 'piece',
  `image` varchar(255) DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `specifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specifications`)),
  `featured` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive','out_of_stock') DEFAULT 'active',
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `review` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms_log`
--

CREATE TABLE `sms_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `phone` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `type` enum('transaction','order','tender','verification','alert','general') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `status` enum('pending','sent','delivered','failed') NOT NULL DEFAULT 'pending',
  `provider` varchar(50) DEFAULT NULL,
  `provider_message_id` varchar(255) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `ticket_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `category` enum('general','order','product','payment','technical','other') NOT NULL DEFAULT 'general',
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `status` enum('open','in_progress','waiting_customer','resolved','closed') NOT NULL DEFAULT 'open',
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenders`
--

CREATE TABLE `tenders` (
  `id` int(11) NOT NULL,
  `tender_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `quantity` varchar(100) DEFAULT NULL,
  `budget_range` varchar(100) DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `status` enum('pending','reviewed','quoted','accepted','rejected','closed') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `quote_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenders`
--

INSERT INTO `tenders` (`id`, `tender_number`, `user_id`, `title`, `description`, `category`, `quantity`, `budget_range`, `deadline`, `status`, `admin_notes`, `quote_amount`, `created_at`, `updated_at`) VALUES
(1, 'TND-20260223-699C51A5A1F38', 1, 'poli', 'popj', 'electronics', '1 piece', 'KES 4,000', '2026-02-24', 'pending', NULL, NULL, '2026-02-23 13:09:57', '2026-02-23 13:09:57'),
(2, 'TND-20260223-699C5970D0E16', 1, 'Iron sheets', 'Deliver them soon.', 'industrial', '1000 pieces', 'KES 40,000 - KES 100,000', '2026-02-26', 'pending', NULL, NULL, '2026-02-23 13:43:12', '2026-02-23 13:43:12'),
(3, 'TND-20260223-699C59713AAA2', 1, 'Iron sheets', 'Deliver them soon.', 'industrial', '1000 pieces', 'KES 40,000 - KES 100,000', '2026-02-26', 'pending', NULL, NULL, '2026-02-23 13:43:13', '2026-02-23 13:43:13');

-- --------------------------------------------------------

--
-- Table structure for table `tender_quotes`
--

CREATE TABLE `tender_quotes` (
  `id` int(11) NOT NULL,
  `tender_id` int(11) NOT NULL,
  `quoted_price` decimal(12,2) NOT NULL,
  `valid_until` date NOT NULL,
  `notes` text DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `status` enum('pending','accepted','rejected','expired') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_messages`
--

CREATE TABLE `ticket_messages` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_internal` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('order','tender','deposit','delivery') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'KES',
  `payment_method` varchar(50) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `status` enum('pending','processing','completed','failed','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `provider_reference` varchar(255) DEFAULT NULL,
  `provider_response` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `company` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','officer','customer','manager','accountant','delivery_guy','vendor') NOT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `company`, `password`, `role`, `status`, `email_verified`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'DEO', 'TEDD', 'marshtedd9@gmail.com', '+254794843896', '', '$2y$10$rFnIf.VmEBVBbvza53uFbeWBsqpwCfv5axx1TbCPHwoavbQbLHnX2', 'customer', '', 1, '2026-02-23 12:24:12', '2026-02-23 15:41:37', '2026-02-23 13:38:55'),
(2, 'Admin', 'User', 'admin@aluora.com', '', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', 0, '2026-02-23 14:57:37', '2026-02-23 16:26:06', '2026-02-23 16:26:06'),
(4, 'Staff', 'Member', 'staff@aluora.com', '+254700000002', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '', '', 0, '2026-02-23 16:13:14', '2026-02-23 16:26:35', NULL),
(5, 'Manager', 'User', 'manager@aluora.com', '+254700000003', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 'active', 0, '2026-02-23 16:13:14', '2026-02-23 16:22:49', '2026-02-23 16:22:49'),
(6, 'Vendor', 'User', 'vendor@aluora.com', '+254700000004', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', 'active', 0, '2026-02-23 16:13:15', '2026-02-23 16:24:12', '2026-02-23 16:24:12'),
(7, 'Accountant', 'User', 'accountant@aluora.com', '+254700000005', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'accountant', 'active', 0, '2026-02-23 16:13:16', '2026-02-23 16:18:14', NULL),
(8, 'Delivery', 'Person', 'delivery@aluora.com', '+254700000006', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'delivery_guy', 'active', 0, '2026-02-23 16:13:16', '2026-02-23 16:17:55', NULL),
(9, 'Customer', 'User', 'customer@aluora.com', '+254700000007', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active', 0, '2026-02-23 16:13:16', '2026-02-23 16:13:16', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `admin_alerts`
--
ALTER TABLE `admin_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_read` (`is_read`),
  ADD KEY `idx_dismissed` (`is_dismissed`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session` (`session_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_rating` (`rating`);

--
-- Indexes for table `delivery_zones`
--
ALTER TABLE `delivery_zones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `email_log`
--
ALTER TABLE `email_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `human_chat_requests`
--
ALTER TABLE `human_chat_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_assigned` (`assigned_to`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_read` (`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_order_number` (`order_number`);

--
-- Indexes for table `order_delivery`
--
ALTER TABLE `order_delivery`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_status` (`delivery_status`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `payment_settings`
--
ALTER TABLE `payment_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_key` (`setting_key`),
  ADD KEY `idx_group` (`setting_group`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_featured` (`featured`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `sms_log`
--
ALTER TABLE `sms_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_number` (`ticket_number`),
  ADD KEY `idx_ticket_number` (`ticket_number`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_assigned` (`assigned_to`);

--
-- Indexes for table `tenders`
--
ALTER TABLE `tenders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tender_number` (`tender_number`),
  ADD KEY `idx_tender_number` (`tender_number`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `tender_quotes`
--
ALTER TABLE `tender_quotes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tender` (`tender_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ticket` (`ticket_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `idx_transaction_id` (`transaction_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_reference` (`reference_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_method` (`payment_method`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_alerts`
--
ALTER TABLE `admin_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `delivery_zones`
--
ALTER TABLE `delivery_zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `email_log`
--
ALTER TABLE `email_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `human_chat_requests`
--
ALTER TABLE `human_chat_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_delivery`
--
ALTER TABLE `order_delivery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payment_settings`
--
ALTER TABLE `payment_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms_log`
--
ALTER TABLE `sms_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tenders`
--
ALTER TABLE `tenders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tender_quotes`
--
ALTER TABLE `tender_quotes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `email_log`
--
ALTER TABLE `email_log`
  ADD CONSTRAINT `email_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `human_chat_requests`
--
ALTER TABLE `human_chat_requests`
  ADD CONSTRAINT `human_chat_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `human_chat_requests_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_delivery`
--
ALTER TABLE `order_delivery`
  ADD CONSTRAINT `order_delivery_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sms_log`
--
ALTER TABLE `sms_log`
  ADD CONSTRAINT `sms_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_tickets_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tenders`
--
ALTER TABLE `tenders`
  ADD CONSTRAINT `tenders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tender_quotes`
--
ALTER TABLE `tender_quotes`
  ADD CONSTRAINT `tender_quotes_ibfk_1` FOREIGN KEY (`tender_id`) REFERENCES `tenders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD CONSTRAINT `ticket_messages_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
