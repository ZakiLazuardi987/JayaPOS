-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 16, 2026 at 03:36 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kopjay_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_redeem_reward` (IN `p_member_id` BIGINT UNSIGNED, IN `p_reedem_id` BIGINT UNSIGNED, IN `p_outlet_id` BIGINT UNSIGNED, IN `p_staff_id` BIGINT UNSIGNED, OUT `p_result` VARCHAR(255))   BEGIN
  DECLARE v_point_cost INT UNSIGNED;
  DECLARE v_current_points INT UNSIGNED;
  DECLARE v_product_id BIGINT UNSIGNED;
  DECLARE v_product_price DECIMAL(12,2);
  DECLARE v_new_order_id BIGINT UNSIGNED;
  
  -- Start transaction
  START TRANSACTION;
  
  -- Get redemption details
  SELECT point_cost, product_id 
  INTO v_point_cost, v_product_id
  FROM reedem 
  WHERE reedem_id = p_reedem_id AND is_active = TRUE;
  
  -- Check if redemption exists
  IF v_point_cost IS NULL THEN
    SET p_result = 'ERROR: Invalid or inactive redemption item';
    ROLLBACK;
  ELSE
    -- Get member's current points
    SELECT current_points INTO v_current_points
    FROM member 
    WHERE member_id = p_member_id;
    
    -- Validate sufficient balance
    IF v_current_points < v_point_cost THEN
      SET p_result = CONCAT('ERROR: Insufficient points. Required: ', v_point_cost, ', Available: ', v_current_points);
      ROLLBACK;
    ELSE
      -- Get product price
      SELECT base_price INTO v_product_price
      FROM products 
      WHERE product_id = v_product_id;
      
      -- Create order with Rp 0 total (redemption order)
      INSERT INTO orders (
        member_id, staff_id, outlet_id, source, order_type, status,
        subtotal, total_final, points_earned
      ) VALUES (
        p_member_id, p_staff_id, p_outlet_id, 'Mobile App', 'click-collect', 'paid',
        0.00, 0.00, 0
      );
      
      SET v_new_order_id = LAST_INSERT_ID();
      
      -- Add order item (with snapshot price but charged as Rp 0)
      INSERT INTO order_items (
        order_id, product_id, quantity, price_at_purchase, points_earned_per_item
      ) VALUES (
        v_new_order_id, v_product_id, 1, v_product_price, 0
      );
      
      -- Deduct points from member
      UPDATE member 
      SET current_points = current_points - v_point_cost
      WHERE member_id = p_member_id;
      
      -- Log the redemption
      INSERT INTO points_history (
        member_id, order_id, points_change, transaction_type, description,
        balance_before, balance_after
      )
      SELECT 
        p_member_id,
        v_new_order_id,
        -v_point_cost,
        'redeemed',
        CONCAT('Redeemed reward: Order #', v_new_order_id),
        current_points + v_point_cost,
        current_points
      FROM member 
      WHERE member_id = p_member_id;
      
      -- Update redemption count
      UPDATE reedem 
      SET redemption_count = redemption_count + 1
      WHERE reedem_id = p_reedem_id;
      
      COMMIT;
      SET p_result = CONCAT('SUCCESS: Order #', v_new_order_id, ' created. Points deducted: ', v_point_cost);
    END IF;
  END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Signature Coffee Milk', 'Menu signature kopi susu', 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(2, 'Coffee Based', 'Olahan minuman kopi', 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(3, 'Soda', 'Minuman bersoda', 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(4, 'Chocolate', 'Minuman cokelat', 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(5, 'Milk Based', 'Minuman berbasis susu', 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(6, 'Tea', 'Berbagai jenis teh', 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(7, 'Tradisional', 'Minuman tradisional', 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(8, 'Pastry', 'Aneka pastry', 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(9, 'Main Course', 'Makanan utama', 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(10, 'Chicken Wings', 'Sayap ayam', 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(11, 'Snack', 'Makanan ringan', 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(12, 'Add - On', 'Tambahan pesanan', 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `discount`
--

CREATE TABLE `discount` (
  `discount_id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Promo code (e.g., NEWUSER10)',
  `type` enum('nominal','percentage') COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` decimal(12,2) NOT NULL,
  `min_purchase` decimal(12,2) DEFAULT '0.00',
  `max_discount` decimal(12,2) DEFAULT NULL COMMENT 'Cap for percentage discounts',
  `usage_limit` int UNSIGNED DEFAULT NULL COMMENT 'Total usage limit',
  `usage_count` int UNSIGNED DEFAULT '0',
  `valid_from` timestamp NULL DEFAULT NULL,
  `valid_until` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discount`
--

INSERT INTO `discount` (`discount_id`, `name`, `code`, `type`, `value`, `min_purchase`, `max_discount`, `usage_limit`, `usage_count`, `valid_from`, `valid_until`, `is_active`) VALUES
(1, 'Diskon Lebaran', 'LEBARAN10', 'percentage', '10.00', '0.00', NULL, NULL, 0, NULL, NULL, 1),
(2, 'Diskon Karyawan', 'KARYAWAN5', 'percentage', '5.00', '0.00', NULL, NULL, 0, NULL, NULL, 1),
(3, 'Diskon Opening', 'OPENING15', 'percentage', '15.00', '0.00', NULL, NULL, 0, NULL, NULL, 1),
(4, 'Diskon Member', 'MEMBER10', 'percentage', '10.00', '0.00', NULL, NULL, 0, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `favorite`
--

CREATE TABLE `favorite` (
  `favorite_id` bigint UNSIGNED NOT NULL,
  `outlet_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `favorite`
--

INSERT INTO `favorite` (`favorite_id`, `outlet_id`, `product_id`, `created_at`) VALUES
(1, 1, 1, '2026-05-05 06:00:47'),
(2, 1, 2, '2026-05-05 06:00:47'),
(3, 1, 3, '2026-05-05 06:00:47'),
(4, 1, 4, '2026-05-05 06:00:47'),
(5, 1, 5, '2026-05-05 06:00:47'),
(6, 1, 6, '2026-05-05 06:00:47'),
(7, 1, 7, '2026-05-05 06:00:47'),
(8, 1, 8, '2026-05-05 06:00:47'),
(9, 1, 9, '2026-05-05 06:00:47'),
(10, 1, 10, '2026-05-05 06:00:47'),
(11, 1, 11, '2026-05-05 06:00:47'),
(12, 1, 12, '2026-05-05 06:00:47'),
(13, 1, 13, '2026-05-05 06:00:47'),
(15, 1, 15, '2026-05-05 06:00:47'),
(16, 1, 16, '2026-05-05 06:00:47'),
(17, 1, 17, '2026-05-05 06:00:47'),
(18, 1, 18, '2026-05-05 06:00:47'),
(19, 1, 19, '2026-05-05 06:00:47'),
(20, 1, 20, '2026-05-05 06:00:47'),
(21, 1, 44, '2026-05-05 07:45:26'),
(22, 1, 21, '2026-05-05 07:45:37'),
(25, 1, 22, '2026-05-05 10:30:08'),
(27, 1, 24, '2026-05-05 13:09:46'),
(28, 1, 47, '2026-05-06 07:58:02'),
(30, 1, 26, '2026-05-08 09:12:57'),
(31, 2, 1, '2026-05-14 11:08:20'),
(32, 2, 3, '2026-05-14 11:08:24'),
(33, 2, 2, '2026-05-14 11:08:27'),
(34, 2, 4, '2026-05-14 11:08:31'),
(35, 2, 42, '2026-05-14 11:08:43'),
(36, 2, 38, '2026-05-14 11:08:52'),
(37, 2, 45, '2026-05-14 11:09:00'),
(38, 2, 43, '2026-05-14 11:09:08');

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `member_id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `fav_menu` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_points` int UNSIGNED DEFAULT '0' COMMENT 'Real-time loyalty point balance',
  `lifetime_points_earned` int UNSIGNED DEFAULT '0' COMMENT 'Historical total for analytics',
  `tier` enum('Bronze','Silver','Gold','Platinum') COLLATE utf8mb4_unicode_ci DEFAULT 'Bronze' COMMENT 'Membership tier',
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `member`
--

INSERT INTO `member` (`member_id`, `name`, `email`, `phone_number`, `birthday`, `fav_menu`, `password`, `current_points`, `lifetime_points_earned`, `tier`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'Davis Hermanto', 'davis@example.com', '081234567890', NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 150, 0, 'Silver', 1, NULL, '2026-04-05 14:28:45', '2026-04-05 14:28:45');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_05_14_023101_create_product_modifier_table', 2),
(5, '2026_05_14_023120_add_group_fields_to_modifier_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `modifier`
--

CREATE TABLE `modifier` (
  `modifier_id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `selection_type` enum('single','multiple') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'single',
  `extra_price` decimal(12,2) DEFAULT '0.00',
  `type` enum('add','remove') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'add',
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `modifier`
--

INSERT INTO `modifier` (`modifier_id`, `name`, `group_name`, `selection_type`, `extra_price`, `type`, `is_active`) VALUES
(1, 'Small', 'Ukuran', 'single', '0.00', 'add', 1),
(2, 'Medium', 'Ukuran', 'single', '5000.00', 'add', 1),
(3, 'Large', 'Ukuran', 'single', '8000.00', 'add', 1),
(4, 'Less Ice', 'Tingkat Es', 'single', '0.00', 'add', 1),
(5, 'Normal Ice', 'Tingkat Es', 'single', '0.00', 'add', 1),
(6, 'Full Ice', 'Tingkat Es', 'single', '0.00', 'add', 1),
(7, 'Less Sugar', 'Tingkat Gula', 'single', '0.00', 'add', 1),
(8, 'Normal Sugar', 'Tingkat Gula', 'single', '0.00', 'add', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` bigint UNSIGNED NOT NULL,
  `member_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Registered member (loyalty eligible)',
  `customer_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Anonymous walk-in customer',
  `staff_id` bigint UNSIGNED NOT NULL COMMENT 'Staff who processed the order',
  `outlet_id` bigint UNSIGNED NOT NULL COMMENT 'Fulfillment location',
  `source` enum('Mobile App','POS - In-Store','POS - GoFood','POS - GrabFood','POS - ShopeeFood','Admin Dashboard','Kiosk') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'POS - In-Store',
  `order_type` enum('dine-in','takeaway','click-collect') COLLATE utf8mb4_unicode_ci DEFAULT 'takeaway',
  `table_number` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','paid','preparing','ready_for_pickup','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `pickup_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Unique code for click-collect verification',
  `subtotal` decimal(12,2) NOT NULL,
  `tax_id` bigint UNSIGNED DEFAULT NULL,
  `service_charge_id` bigint UNSIGNED DEFAULT NULL,
  `discount_id` bigint UNSIGNED DEFAULT NULL,
  `discount_amount` decimal(12,2) DEFAULT '0.00',
  `total_final` decimal(12,2) NOT NULL,
  `points_earned` int UNSIGNED DEFAULT '0' COMMENT 'Total points earned from this order (Product-Weighted sum)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `paid_at` timestamp NULL DEFAULT NULL,
  `ready_at` timestamp NULL DEFAULT NULL COMMENT 'When order became ready for pickup',
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `orders`
--
DELIMITER $$
CREATE TRIGGER `trg_credit_points_after_payment` AFTER UPDATE ON `orders` FOR EACH ROW BEGIN
  -- Only execute when status changes to 'paid' and member exists
  IF NEW.status = 'paid' AND OLD.status != 'paid' AND NEW.member_id IS NOT NULL THEN
    
    -- Calculate total points for the order
    SET @total_points = (
      SELECT COALESCE(SUM(total_points_for_line), 0)
      FROM order_items
      WHERE order_id = NEW.order_id
    );
    
    -- Update order's points_earned field
    UPDATE orders 
    SET points_earned = @total_points 
    WHERE order_id = NEW.order_id;
    
    -- Credit points to member's balance
    UPDATE member 
    SET 
      current_points = current_points + @total_points,
      lifetime_points_earned = lifetime_points_earned + @total_points
    WHERE member_id = NEW.member_id;
    
    -- Log the transaction in points_history
    INSERT INTO points_history (
      member_id, 
      order_id, 
      points_change, 
      transaction_type, 
      description,
      balance_before,
      balance_after
    )
    SELECT 
      NEW.member_id,
      NEW.order_id,
      @total_points,
      'earned',
      CONCAT('Points earned from Order #', NEW.order_id),
      current_points - @total_points,
      current_points
    FROM member 
    WHERE member_id = NEW.member_id;
    
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `quantity` int UNSIGNED NOT NULL DEFAULT '1',
  `price_at_purchase` decimal(12,2) NOT NULL COMMENT 'Snapshot of price (prevents retroactive changes)',
  `points_earned_per_item` int UNSIGNED DEFAULT '0' COMMENT 'Snapshot of product.earning_points at purchase time',
  `total_points_for_line` int UNSIGNED GENERATED ALWAYS AS ((`quantity` * `points_earned_per_item`)) STORED,
  `parent_item_id` bigint UNSIGNED DEFAULT NULL COMMENT 'For bundled products or combo meals',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `order_items`
--
DELIMITER $$
CREATE TRIGGER `trg_set_earning_points_on_order_item` BEFORE INSERT ON `order_items` FOR EACH ROW BEGIN
  -- Snapshot the earning_points from the product catalog
  SET NEW.points_earned_per_item = (
    SELECT earning_points 
    FROM products 
    WHERE product_id = NEW.product_id
  );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `order_item_modifier`
--

CREATE TABLE `order_item_modifier` (
  `order_item_modifier_id` bigint UNSIGNED NOT NULL,
  `order_item_id` bigint UNSIGNED NOT NULL,
  `modifier_id` bigint UNSIGNED NOT NULL,
  `price_added` decimal(12,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `outlet`
--

CREATE TABLE `outlet` (
  `outlet_id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive','maintenance') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `outlet`
--

INSERT INTO `outlet` (`outlet_id`, `name`, `address`, `latitude`, `longitude`, `phone`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Toko Kopi Jaya - Malang Town Square', 'Jl. Veteran No. 2, Malang', '-7.9666200', '112.6326300', NULL, 'active', '2026-04-05 14:28:45', '2026-04-05 14:28:45'),
(2, 'Toko Kopi Jaya - Klojen', 'Jl. Pajajaran No.25D, Klojen, Malang', '-7.9755998', '112.6365967', '+62 811-3333-2323', 'active', '2026-05-14 11:00:41', '2026-05-14 11:00:41');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `payment_method` enum('QRIS','GoPay','OVO','Dana','ShopeePay','Cash','Debit Card','Credit Card','Bank Transfer') COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_gateway` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'midtrans, xendit, manual',
  `transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Gateway transaction reference',
  `payment_url` text COLLATE utf8mb4_unicode_ci COMMENT 'URL for payment page (QRIS, etc.)',
  `payment_response` json DEFAULT NULL COMMENT 'Full webhook/callback response from gateway',
  `status` enum('pending','success','failed','expired','refunded') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `paid_at` timestamp NULL DEFAULT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `points_history`
--

CREATE TABLE `points_history` (
  `points_history_id` bigint UNSIGNED NOT NULL,
  `member_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Reference to order if points earned/redeemed via purchase',
  `points_change` int NOT NULL COMMENT 'Positive for earn, negative for burn/expiry',
  `transaction_type` enum('earned','redeemed','expired','adjusted','refunded','bonus') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Human-readable transaction note',
  `balance_before` int UNSIGNED NOT NULL COMMENT 'Snapshot of points before transaction',
  `balance_after` int UNSIGNED NOT NULL COMMENT 'Snapshot of points after transaction',
  `created_by` bigint UNSIGNED DEFAULT NULL COMMENT 'Staff ID if manually adjusted',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `base_price` decimal(12,2) NOT NULL,
  `img_url` text COLLATE utf8mb4_unicode_ci,
  `earning_points` int UNSIGNED DEFAULT '0' COMMENT 'Points earned per unit purchase (Product-Weighted Algorithm)',
  `is_available` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `name`, `description`, `base_price`, `img_url`, `earning_points`, `is_available`, `created_at`, `updated_at`) VALUES
(1, 1, 'Kopi Susu Mantap Jaya', 'double shoot espresso', '15000.00', 'assets/products/kopi_susu_jaya.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(2, 1, 'Kopi Susu Jaya', 'single shoot espresso', '15000.00', 'assets/products/kopi_susu_jaya.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(3, 1, 'Kopi Susu Gula Aren', '', '18000.00', 'assets/products/kopi_jaya_gula_aren.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(4, 1, 'Kopi Susu Jaya Oat Milk', '', '24000.00', 'assets/products/kopi_susu_jaya.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(5, 2, 'Tubruk', '', '15000.00', 'assets/products/minuman_hangat.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(6, 2, 'Tubruk Susu', '', '15000.00', 'assets/products/minuman_hangat.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(7, 2, 'Americano', '', '15000.00', 'assets/products/americano.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(8, 2, 'Cappucino', '', '26000.00', 'assets/products/coffee_late.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(9, 2, 'Caffe Latte', '', '26000.00', 'assets/products/coffee_late.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(10, 2, 'Mochacino', '', '28000.00', 'assets/products/coffee_late.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(11, 3, 'Coffee', '', '15000.00', 'assets/products/soda_kopi.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(12, 3, 'Sarsaparilla', '', '15000.00', 'assets/products/soda_sarsa.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(13, 3, 'Blackcurrant', '', '15000.00', 'assets/products/soda_black.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(14, 3, 'Lychee', '', '15000.00', 'assets/products/soda_leci.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(15, 4, 'Almond', '', '15000.00', 'assets/products/cokelat.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(16, 4, 'Hazelnut', '', '15000.00', 'assets/products/cokelat.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(17, 4, 'Original', '', '15000.00', 'assets/products/cokelat.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(18, 5, 'Green Tea Latte', '', '15000.00', 'assets/products/greentea_latte.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(19, 5, 'Thai Tea', '', '15000.00', 'assets/products/thai_tea.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(20, 5, 'Rose Tea', '', '15000.00', 'assets/products/rose_tea.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(21, 6, 'Chamomile', '', '15000.00', 'assets/products/lemon_tea.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(22, 6, 'Green Tea Jasmine', '', '15000.00', 'assets/products/lemon_tea.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(23, 6, 'Black Tea Jasmine', '', '15000.00', 'assets/products/black_tea.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(24, 6, 'Lemon Tea', '', '18000.00', 'assets/products/lemon_tea.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(25, 6, 'Butterfly Pea Flower', '', '18000.00', 'assets/products/butterfly.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(26, 6, 'Green Tea Lemon Mint', '', '18000.00', 'assets/products/greentea_lemon_mint.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(27, 6, 'Ice Shaken Apple Tea', '', '18000.00', 'assets/products/apple_tea.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(28, 6, 'Ice Shaken Peach Tea', '', '18000.00', 'assets/products/peach.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(29, 7, 'Beras Kencur', '', '15000.00', 'assets/products/beras_kencur.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(30, 7, 'Kunir Asem', '', '15000.00', 'assets/products/kunir_asam.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(31, 7, 'Ginger Ale', '', '15000.00', 'assets/products/ginger_ale.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(32, 8, 'Danish Choco', '', '15000.00', NULL, 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(33, 8, 'Danish Vanilla', '', '15000.00', NULL, 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(34, 9, 'Honey Butter Rice Chicken', '', '32000.00', NULL, 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(35, 9, 'Penang Curry Chicken', '', '32000.00', NULL, 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(36, 9, 'Khanpunggi Rice', '', '32000.00', NULL, 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(37, 9, 'Nasi Uduk Khas Jaya', '', '30000.00', NULL, 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(38, 9, 'Nasi Goreng Kalio', '', '30000.00', NULL, 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(39, 9, 'Chicken Spicy Mala', '', '30000.00', NULL, 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(40, 9, 'Mie Maggie Tsedap', '', '30000.00', NULL, 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(41, 11, 'Handcut Fries Coffee Sauce', '', '18000.00', 'assets/products/hand_cut_fries.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(42, 11, 'Potato Wedges', '', '18000.00', 'assets/products/potato_wedges.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(43, 11, 'Nachos Pargos', '', '28000.00', 'assets/products/nachos_pargos.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(44, 11, 'Tahu Bakso', '', '18000.00', 'assets/products/tahu_bakso.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(45, 11, 'Gyoza', '', '20000.00', 'assets/products/gyoza.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(46, 11, 'Spring Roll', '', '20000.00', 'assets/products/spring_roll.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(47, 11, 'Pop Corn Classic', '', '20000.00', 'assets/products/popcorn.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(48, 11, 'Brownies', '', '10000.00', NULL, 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(49, 10, 'Chicken Wings Isi 4', '', '20000.00', 'assets/products/chicken_wings_2.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(50, 10, 'Chicken Wings Isi 6', '', '30000.00', 'assets/products/chicken_wings_2.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(51, 10, 'Chicken Wings Isi 8', '', '36000.00', 'assets/products/chicken_wings_2.jpg', 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(52, 12, 'Air Mineral', '', '5000.00', NULL, 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(53, 12, 'Extra Nasi', '', '5000.00', NULL, 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(54, 12, 'Extra Sunny Side Up', '', '5000.00', NULL, 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(55, 12, 'Extra Saus BBQ', '', '5000.00', NULL, 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(56, 12, 'Extra Saus Jaya', '', '5000.00', NULL, 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(57, 12, 'Extra Saus Sambal', '', '5000.00', NULL, 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47'),
(58, 12, 'Espresso', '', '8000.00', NULL, 0, 1, '2026-05-05 06:00:47', '2026-05-05 06:00:47');

-- --------------------------------------------------------

--
-- Table structure for table `product_modifier`
--

CREATE TABLE `product_modifier` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `modifier_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_modifier`
--

INSERT INTO `product_modifier` (`id`, `product_id`, `modifier_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(2, 1, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(3, 1, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(4, 1, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(5, 1, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(6, 1, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(7, 1, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(8, 1, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(9, 2, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(10, 2, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(11, 2, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(12, 2, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(13, 2, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(14, 2, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(15, 2, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(16, 2, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(17, 3, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(18, 3, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(19, 3, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(20, 3, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(21, 3, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(22, 3, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(23, 3, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(24, 3, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(25, 4, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(26, 4, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(27, 4, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(28, 4, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(29, 4, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(30, 4, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(31, 4, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(32, 4, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(33, 5, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(34, 5, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(35, 5, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(36, 5, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(37, 5, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(38, 5, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(39, 5, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(40, 5, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(41, 6, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(42, 6, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(43, 6, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(44, 6, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(45, 6, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(46, 6, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(47, 6, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(48, 6, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(49, 7, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(50, 7, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(51, 7, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(52, 7, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(53, 7, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(54, 7, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(55, 7, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(56, 7, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(57, 8, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(58, 8, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(59, 8, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(60, 8, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(61, 8, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(62, 8, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(63, 8, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(64, 8, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(65, 9, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(66, 9, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(67, 9, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(68, 9, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(69, 9, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(70, 9, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(71, 9, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(72, 9, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(73, 10, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(74, 10, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(75, 10, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(76, 10, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(77, 10, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(78, 10, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(79, 10, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(80, 10, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(81, 11, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(82, 11, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(83, 11, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(84, 11, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(85, 11, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(86, 11, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(87, 11, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(88, 11, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(89, 12, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(90, 12, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(91, 12, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(92, 12, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(93, 12, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(94, 12, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(95, 12, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(96, 12, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(97, 13, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(98, 13, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(99, 13, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(100, 13, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(101, 13, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(102, 13, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(103, 13, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(104, 13, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(105, 14, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(106, 14, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(107, 14, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(108, 14, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(109, 14, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(110, 14, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(111, 14, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(112, 14, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(113, 15, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(114, 15, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(115, 15, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(116, 15, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(117, 15, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(118, 15, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(119, 15, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(120, 15, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(121, 16, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(122, 16, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(123, 16, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(124, 16, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(125, 16, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(126, 16, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(127, 16, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(128, 16, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(129, 17, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(130, 17, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(131, 17, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(132, 17, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(133, 17, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(134, 17, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(135, 17, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(136, 17, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(137, 18, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(138, 18, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(139, 18, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(140, 18, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(141, 18, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(142, 18, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(143, 18, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(144, 18, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(145, 19, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(146, 19, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(147, 19, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(148, 19, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(149, 19, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(150, 19, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(151, 19, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(152, 19, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(153, 20, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(154, 20, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(155, 20, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(156, 20, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(157, 20, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(158, 20, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(159, 20, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(160, 20, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(161, 21, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(162, 21, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(163, 21, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(164, 21, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(165, 21, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(166, 21, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(167, 21, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(168, 21, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(169, 22, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(170, 22, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(171, 22, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(172, 22, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(173, 22, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(174, 22, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(175, 22, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(176, 22, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(177, 23, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(178, 23, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(179, 23, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(180, 23, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(181, 23, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(182, 23, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(183, 23, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(184, 23, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(185, 24, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(186, 24, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(187, 24, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(188, 24, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(189, 24, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(190, 24, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(191, 24, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(192, 24, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(193, 25, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(194, 25, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(195, 25, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(196, 25, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(197, 25, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(198, 25, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(199, 25, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(200, 25, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(201, 26, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(202, 26, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(203, 26, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(204, 26, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(205, 26, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(206, 26, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(207, 26, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(208, 26, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(209, 27, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(210, 27, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(211, 27, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(212, 27, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(213, 27, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(214, 27, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(215, 27, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(216, 27, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(217, 28, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(218, 28, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(219, 28, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(220, 28, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(221, 28, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(222, 28, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(223, 28, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(224, 28, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(225, 29, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(226, 29, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(227, 29, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(228, 29, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(229, 29, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(230, 29, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(231, 29, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(232, 29, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(233, 30, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(234, 30, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(235, 30, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(236, 30, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(237, 30, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(238, 30, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(239, 30, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(240, 30, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(241, 31, 1, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(242, 31, 2, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(243, 31, 3, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(244, 31, 4, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(245, 31, 5, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(246, 31, 6, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(247, 31, 7, '2026-05-13 19:49:27', '2026-05-13 19:49:27'),
(248, 31, 8, '2026-05-13 19:49:27', '2026-05-13 19:49:27');

-- --------------------------------------------------------

--
-- Table structure for table `reedem`
--

CREATE TABLE `reedem` (
  `reedem_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `point_cost` int UNSIGNED NOT NULL COMMENT 'Points required to redeem this item (Burn Mechanism)',
  `is_active` tinyint(1) DEFAULT '1' COMMENT 'Admin can toggle redemption availability',
  `stock_limit` int UNSIGNED DEFAULT NULL COMMENT 'Optional: limit redemption quantity',
  `redemption_count` int UNSIGNED DEFAULT '0' COMMENT 'Track popularity',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_charge`
--

CREATE TABLE `service_charge` (
  `service_charge_id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('nominal','percentage') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percentage',
  `value` decimal(12,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('iL9qJUmsKqlJ2oxSp6kmVr5r3AJ6odSq07yNg7wS', 12, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiVElPTVg2MDNsM2xqcjFJMEVXMFRZVkhHM3lOWjNCN0RIQXFpRkZIRCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyNToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL3BvcyI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjMzOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvcG9zL2xpYnJhcnkiO3M6NToicm91dGUiO3M6MTE6InBvcy5saWJyYXJ5Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTI7czoxMzoiYWN0aXZlX291dGxldCI7czoxOiIyIjt9', 1778901870);

-- --------------------------------------------------------

--
-- Table structure for table `shift`
--

CREATE TABLE `shift` (
  `shift_id` bigint UNSIGNED NOT NULL,
  `staff_id` bigint UNSIGNED NOT NULL,
  `outlet_id` bigint UNSIGNED NOT NULL,
  `start_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `end_time` timestamp NULL DEFAULT NULL,
  `cash_in_hand` decimal(12,2) DEFAULT '0.00' COMMENT 'Starting cash',
  `total_cash_received` decimal(12,2) DEFAULT '0.00',
  `total_cash_out` decimal(12,2) DEFAULT '0.00',
  `final_cash` decimal(12,2) DEFAULT '0.00',
  `discrepancy` decimal(12,2) GENERATED ALWAYS AS ((((`cash_in_hand` + `total_cash_received`) - `total_cash_out`) - `final_cash`)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','cashier','manager','barista') COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `outlet_id` bigint UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `name`, `phone`, `role`, `username`, `password`, `outlet_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', '0', 'admin', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, '2026-04-05 14:28:45', '2026-04-05 14:28:45'),
(2, 'Cashier User', '0', 'cashier', 'cashier01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, '2026-04-05 14:28:45', '2026-04-05 14:28:45'),
(7, 'testmalay', '+60123456879', 'cashier', 'malay@gmail.com', '$2y$12$m3NE6Y0mP8lKZqynCFL6vu/77ud200OPMcCIVvBvFEwtovO.56Eze', NULL, 0, '2026-04-20 03:03:06', '2026-04-20 03:03:06'),
(10, 'chandelier', '+6281234567891', 'cashier', 'chandelier@gmail.com', '$2y$12$zB6twjuziPSwwQR1JCtpZOJV.7W0SyJLjHg/TMXBxKNAW9x1SByau', NULL, 0, '2026-05-05 06:16:01', '2026-05-05 06:16:01'),
(11, 'Test User', '+6281234567890', 'cashier', 'test@example.com', '$2y$12$FMm0T1ySiPUHWmmbSe6C2uLZkwUGgAsrhX6L5j52Ybi6bXG0X.YYq', NULL, 0, '2026-05-13 19:43:15', '2026-05-13 19:43:15'),
(12, 'testing', '+628123456789', 'cashier', 'testing@gmail.com', '$2y$12$S8NoSydJiHi/uUeWAkAIrO.HNbvtPcv/WxlnexwxMdex5HsviZABu', 2, 1, '2026-05-14 03:57:02', '2026-05-14 11:02:56');

-- --------------------------------------------------------

--
-- Table structure for table `tax`
--

CREATE TABLE `tax` (
  `tax_id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('nominal','percentage') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percentage',
  `value` decimal(12,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tax`
--

INSERT INTO `tax` (`tax_id`, `name`, `type`, `value`, `is_active`) VALUES
(1, 'PPN 11%', 'percentage', '11.00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_member_loyalty_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_member_loyalty_summary` (
`current_points` int unsigned
,`email` varchar(100)
,`last_order_date` timestamp
,`lifetime_points_earned` int unsigned
,`member_id` bigint unsigned
,`name` varchar(100)
,`tier` enum('Bronze','Silver','Gold','Platinum')
,`total_orders` bigint
,`total_points_from_orders` decimal(32,0)
,`total_spent` decimal(34,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_product_performance`
-- (See below for the actual view)
--
CREATE TABLE `v_product_performance` (
`base_price` decimal(12,2)
,`earning_points` int unsigned
,`name` varchar(100)
,`product_id` bigint unsigned
,`times_purchased` bigint
,`total_points_issued` decimal(32,0)
,`total_quantity_sold` decimal(32,0)
,`total_revenue` decimal(44,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_sales_by_source`
-- (See below for the actual view)
--
CREATE TABLE `v_sales_by_source` (
`avg_order_value` decimal(16,6)
,`order_count` bigint
,`registered_member_orders` decimal(23,0)
,`sale_date` date
,`source` enum('Mobile App','POS - In-Store','POS - GoFood','POS - GrabFood','POS - ShopeeFood','Admin Dashboard','Kiosk')
,`total_points_issued` decimal(32,0)
,`total_revenue` decimal(34,2)
);

-- --------------------------------------------------------

--
-- Structure for view `v_member_loyalty_summary`
--
DROP TABLE IF EXISTS `v_member_loyalty_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_member_loyalty_summary`  AS SELECT `m`.`member_id` AS `member_id`, `m`.`name` AS `name`, `m`.`email` AS `email`, `m`.`current_points` AS `current_points`, `m`.`lifetime_points_earned` AS `lifetime_points_earned`, `m`.`tier` AS `tier`, count(distinct `o`.`order_id`) AS `total_orders`, coalesce(sum(`o`.`total_final`),0) AS `total_spent`, coalesce(sum(`o`.`points_earned`),0) AS `total_points_from_orders`, max(`o`.`created_at`) AS `last_order_date` FROM (`member` `m` left join `orders` `o` on((`m`.`member_id` = `o`.`member_id`))) GROUP BY `m`.`member_id``member_id`  ;

-- --------------------------------------------------------

--
-- Structure for view `v_product_performance`
--
DROP TABLE IF EXISTS `v_product_performance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_product_performance`  AS SELECT `p`.`product_id` AS `product_id`, `p`.`name` AS `name`, `p`.`earning_points` AS `earning_points`, `p`.`base_price` AS `base_price`, count(`oi`.`order_item_id`) AS `times_purchased`, sum(`oi`.`quantity`) AS `total_quantity_sold`, sum((`oi`.`quantity` * `oi`.`price_at_purchase`)) AS `total_revenue`, sum(`oi`.`total_points_for_line`) AS `total_points_issued` FROM (`products` `p` left join `order_items` `oi` on((`p`.`product_id` = `oi`.`product_id`))) GROUP BY `p`.`product_id` ORDER BY `total_revenue` AS `DESCdesc` ASC  ;

-- --------------------------------------------------------

--
-- Structure for view `v_sales_by_source`
--
DROP TABLE IF EXISTS `v_sales_by_source`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_sales_by_source`  AS SELECT `o`.`source` AS `source`, cast(`o`.`created_at` as date) AS `sale_date`, count(`o`.`order_id`) AS `order_count`, sum(`o`.`total_final`) AS `total_revenue`, avg(`o`.`total_final`) AS `avg_order_value`, sum((case when (`o`.`member_id` is not null) then 1 else 0 end)) AS `registered_member_orders`, sum(`o`.`points_earned`) AS `total_points_issued` FROM `orders` AS `o` WHERE (`o`.`status` in ('completed','paid')) GROUP BY `o`.`source`, cast(`o`.`created_at` as date) ORDER BY `sale_date` DESC, `total_revenue` AS `DESCdesc` ASC  ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `idx_category_active` (`is_active`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `discount`
--
ALTER TABLE `discount`
  ADD PRIMARY KEY (`discount_id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD UNIQUE KEY `unique_discount_code` (`code`);

--
-- Indexes for table `favorite`
--
ALTER TABLE `favorite`
  ADD PRIMARY KEY (`favorite_id`),
  ADD UNIQUE KEY `unique_outlet_favorite` (`outlet_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone_number` (`phone_number`),
  ADD KEY `idx_member_email` (`email`),
  ADD KEY `idx_member_phone` (`phone_number`),
  ADD KEY `idx_member_tier` (`tier`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `modifier`
--
ALTER TABLE `modifier`
  ADD PRIMARY KEY (`modifier_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `pickup_code` (`pickup_code`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `outlet_id` (`outlet_id`),
  ADD KEY `tax_id` (`tax_id`),
  ADD KEY `service_charge_id` (`service_charge_id`),
  ADD KEY `discount_id` (`discount_id`),
  ADD KEY `idx_order_member` (`member_id`),
  ADD KEY `idx_order_source` (`source`),
  ADD KEY `idx_order_status` (`status`),
  ADD KEY `idx_order_created` (`created_at`),
  ADD KEY `idx_pickup_code` (`pickup_code`),
  ADD KEY `idx_orders_member_status_created` (`member_id`,`status`,`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `parent_item_id` (`parent_item_id`),
  ADD KEY `idx_order_items_order` (`order_id`),
  ADD KEY `idx_order_items_product` (`product_id`),
  ADD KEY `idx_order_items_product_created` (`product_id`,`created_at`);

--
-- Indexes for table `order_item_modifier`
--
ALTER TABLE `order_item_modifier`
  ADD PRIMARY KEY (`order_item_modifier_id`),
  ADD KEY `modifier_id` (`modifier_id`),
  ADD KEY `idx_modifier_order_item` (`order_item_id`);

--
-- Indexes for table `outlet`
--
ALTER TABLE `outlet`
  ADD PRIMARY KEY (`outlet_id`),
  ADD KEY `idx_outlet_status` (`status`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `idx_payment_order` (`order_id`),
  ADD KEY `idx_payment_status` (`status`),
  ADD KEY `idx_payment_transaction` (`transaction_id`);

--
-- Indexes for table `points_history`
--
ALTER TABLE `points_history`
  ADD PRIMARY KEY (`points_history_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_points_history_member` (`member_id`),
  ADD KEY `idx_points_history_type` (`transaction_type`),
  ADD KEY `idx_points_history_created` (`created_at`),
  ADD KEY `idx_points_history_member_created` (`member_id`,`created_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_product_category` (`category_id`),
  ADD KEY `idx_product_available` (`is_available`);

--
-- Indexes for table `product_modifier`
--
ALTER TABLE `product_modifier`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_modifier_product_id_modifier_id_unique` (`product_id`,`modifier_id`),
  ADD KEY `product_modifier_modifier_id_foreign` (`modifier_id`);

--
-- Indexes for table `reedem`
--
ALTER TABLE `reedem`
  ADD PRIMARY KEY (`reedem_id`),
  ADD UNIQUE KEY `unique_redeemable_product` (`product_id`),
  ADD KEY `idx_reedem_active` (`is_active`);

--
-- Indexes for table `service_charge`
--
ALTER TABLE `service_charge`
  ADD PRIMARY KEY (`service_charge_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `shift`
--
ALTER TABLE `shift`
  ADD PRIMARY KEY (`shift_id`),
  ADD KEY `outlet_id` (`outlet_id`),
  ADD KEY `idx_shift_staff` (`staff_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `unique_username` (`username`),
  ADD KEY `outlet_id` (`outlet_id`),
  ADD KEY `idx_staff_role` (`role`);

--
-- Indexes for table `tax`
--
ALTER TABLE `tax`
  ADD PRIMARY KEY (`tax_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `discount`
--
ALTER TABLE `discount`
  MODIFY `discount_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `favorite`
--
ALTER TABLE `favorite`
  MODIFY `favorite_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `member_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `modifier`
--
ALTER TABLE `modifier`
  MODIFY `modifier_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_item_modifier`
--
ALTER TABLE `order_item_modifier`
  MODIFY `order_item_modifier_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `outlet`
--
ALTER TABLE `outlet`
  MODIFY `outlet_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `points_history`
--
ALTER TABLE `points_history`
  MODIFY `points_history_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `product_modifier`
--
ALTER TABLE `product_modifier`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=249;

--
-- AUTO_INCREMENT for table `reedem`
--
ALTER TABLE `reedem`
  MODIFY `reedem_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `service_charge`
--
ALTER TABLE `service_charge`
  MODIFY `service_charge_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shift`
--
ALTER TABLE `shift`
  MODIFY `shift_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tax`
--
ALTER TABLE `tax`
  MODIFY `tax_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `member` (`member_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`outlet_id`) REFERENCES `outlet` (`outlet_id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `orders_ibfk_5` FOREIGN KEY (`tax_id`) REFERENCES `tax` (`tax_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_6` FOREIGN KEY (`service_charge_id`) REFERENCES `service_charge` (`service_charge_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_7` FOREIGN KEY (`discount_id`) REFERENCES `discount` (`discount_id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`parent_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_item_modifier`
--
ALTER TABLE `order_item_modifier`
  ADD CONSTRAINT `order_item_modifier_ibfk_1` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_item_modifier_ibfk_2` FOREIGN KEY (`modifier_id`) REFERENCES `modifier` (`modifier_id`) ON DELETE RESTRICT;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `points_history`
--
ALTER TABLE `points_history`
  ADD CONSTRAINT `points_history_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `member` (`member_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `points_history_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `points_history_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `staff` (`staff_id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE RESTRICT;

--
-- Constraints for table `product_modifier`
--
ALTER TABLE `product_modifier`
  ADD CONSTRAINT `product_modifier_modifier_id_foreign` FOREIGN KEY (`modifier_id`) REFERENCES `modifier` (`modifier_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_modifier_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `reedem`
--
ALTER TABLE `reedem`
  ADD CONSTRAINT `reedem_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `shift`
--
ALTER TABLE `shift`
  ADD CONSTRAINT `shift_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `shift_ibfk_2` FOREIGN KEY (`outlet_id`) REFERENCES `outlet` (`outlet_id`) ON DELETE RESTRICT;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`outlet_id`) REFERENCES `outlet` (`outlet_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
