-- Syr AiX Digital Menu Platform - Database Schema
-- Complete MySQL database structure with sample data

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database
CREATE DATABASE IF NOT EXISTS `syr_aix_menu` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `syr_aix_menu`;

-- --------------------------------------------------------
-- Table: admins (Admin users)
-- --------------------------------------------------------
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin (password: admin123)
INSERT INTO `admins` (`username`, `password`, `email`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@syr-aix.com');

-- --------------------------------------------------------
-- Table: settings (Restaurant settings)
-- --------------------------------------------------------
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_name_ar` varchar(100) NOT NULL,
  `restaurant_name_en` varchar(100) NOT NULL,
  `restaurant_name_tr` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 10.00,
  `currency` varchar(10) NOT NULL DEFAULT '$',
  `theme_color` varchar(7) NOT NULL DEFAULT '#C0D906',
  `welcome_message_ar` text,
  `welcome_message_en` text,
  `welcome_message_tr` text,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address_ar` text,
  `address_en` text,
  `address_tr` text,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `settings` (`restaurant_name_ar`, `restaurant_name_en`, `restaurant_name_tr`, `tax_rate`, `currency`, `welcome_message_ar`, `welcome_message_en`, `welcome_message_tr`) VALUES
('ذا جورميه بيسترو', 'The Gourmet Bistro', 'Gourmet Bistro', 10.00, '$', 'أهلاً بكم في مطعمنا', 'Welcome to Our Restaurant', 'Restoranımıza Hoş Geldiniz');

-- --------------------------------------------------------
-- Table: mandatory_items (Mandatory items added to every order)
-- --------------------------------------------------------
CREATE TABLE `mandatory_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_ar` varchar(100) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `name_tr` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample mandatory items
INSERT INTO `mandatory_items` (`name_ar`, `name_en`, `name_tr`, `price`, `active`, `sort_order`) VALUES
('مياه معدنية', 'Mineral Water', 'Maden Suyu', 1.00, 1, 1),
('محارم', 'Napkins', 'Peçete', 0.50, 1, 2);

-- --------------------------------------------------------
-- Table: categories (Menu categories)
-- --------------------------------------------------------
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_ar` varchar(100) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `name_tr` varchar(100) NOT NULL,
  `description_ar` text,
  `description_en` text,
  `description_tr` text,
  `icon` varchar(50) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample categories
INSERT INTO `categories` (`name_ar`, `name_en`, `name_tr`, `icon`, `sort_order`, `active`) VALUES
('المقبلات', 'Appetizers', 'Başlangıçlar', '🥗', 1, 1),
('الوجبات الرئيسية', 'Main Courses', 'Ana Yemekler', '🍽️', 2, 1),
('المشروبات', 'Beverages', 'İçecekler', '🥤', 3, 1),
('الحلويات', 'Desserts', 'Tatlılar', '🍰', 4, 1),
('الأراكيل', 'Shisha', 'Nargile', '💨', 5, 1);

-- --------------------------------------------------------
-- Table: products (Menu items)
-- --------------------------------------------------------
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name_ar` varchar(150) NOT NULL,
  `name_en` varchar(150) NOT NULL,
  `name_tr` varchar(150) NOT NULL,
  `description_ar` text,
  `description_en` text,
  `description_tr` text,
  `ingredients_ar` text,
  `ingredients_en` text,
  `ingredients_tr` text,
  `health_info_ar` text,
  `health_info_en` text,
  `health_info_tr` text,
  `preparation_method` text,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `calories` int(11) DEFAULT NULL,
  `allergens` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample products
INSERT INTO `products` (`category_id`, `name_ar`, `name_en`, `name_tr`, `description_ar`, `price`, `calories`, `is_featured`, `active`) VALUES
(1, 'حمص بالزيت', 'Hummus with Oil', 'Zeytinyağlı Humus', 'حمص لبناني فاخر مع زيت الزيتون', 5.00, 250, 1, 1),
(1, 'فتوش', 'Fattoush', 'Fattuş', 'سلطة فتوش طازجة مع خبز محمص', 6.00, 180, 0, 1),
(2, 'كبسة لحم', 'Lamb Kabsa', 'Kuzu Kabsa', 'كبسة لحم ضأن على الطريقة العربية', 15.00, 650, 1, 1),
(2, 'شاورما دجاج', 'Chicken Shawarma', 'Tavuk Döner', 'شاورما دجاج متبلة مع الثومية', 8.00, 450, 1, 1),
(3, 'عصير برتقال', 'Orange Juice', 'Portakal Suyu', 'عصير برتقال طازج', 4.00, 120, 0, 1),
(3, 'قهوة عربية', 'Arabic Coffee', 'Arap Kahvesi', 'قهوة عربية تقليدية', 3.00, 5, 0, 1),
(4, 'كنافة بالجبن', 'Cheese Kunafa', 'Peynirli Künefe', 'كنافة ساخنة بالجبن العكاوي', 7.00, 400, 1, 1),
(4, 'بقلاوة', 'Baklava', 'Baklava', 'بقلاوة بالفستق الحلبي', 6.00, 350, 0, 1),
(5, 'أركيلة تفاح', 'Apple Shisha', 'Elma Nargile', 'أركيلة بنكهة التفاح المنعش', 10.00, 0, 0, 1),
(5, 'أركيلة نعناع', 'Mint Shisha', 'Nane Nargile', 'أركيلة بنكهة النعناع', 10.00, 0, 0, 1);

-- --------------------------------------------------------
-- Table: product_images (Multiple images per product)
-- --------------------------------------------------------
CREATE TABLE `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: product_addons (Add-ons for products)
-- --------------------------------------------------------
CREATE TABLE `product_addons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `name_ar` varchar(100) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `name_tr` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_addons_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample addons
INSERT INTO `product_addons` (`product_id`, `name_ar`, `name_en`, `name_tr`, `price`, `active`) VALUES
(3, 'جبنة إضافية', 'Extra Cheese', 'Ekstra Peynir', 2.00, 1),
(3, 'صوص ثوم', 'Garlic Sauce', 'Sarımsak Sosu', 0.50, 1),
(4, 'جبنة إضافية', 'Extra Cheese', 'Ekstra Peynir', 2.00, 1),
(9, 'فحم إضافي', 'Extra Charcoal', 'Ekstra Kömür', 3.00, 1);

-- --------------------------------------------------------
-- Table: tables (Restaurant tables)
-- --------------------------------------------------------
CREATE TABLE `tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_number` varchar(20) NOT NULL,
  `token` varchar(64) NOT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','occupied') NOT NULL DEFAULT 'active',
  `capacity` int(11) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `table_number` (`table_number`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample tables with unique tokens
INSERT INTO `tables` (`table_number`, `token`, `status`, `capacity`, `location`) VALUES
('1', MD5(CONCAT(RAND(), NOW(), 'table1')), 'active', 4, 'Indoor'),
('2', MD5(CONCAT(RAND(), NOW(), 'table2')), 'active', 4, 'Indoor'),
('3', MD5(CONCAT(RAND(), NOW(), 'table3')), 'active', 6, 'Outdoor'),
('4', MD5(CONCAT(RAND(), NOW(), 'table4')), 'active', 2, 'Bar'),
('5', MD5(CONCAT(RAND(), NOW(), 'table5')), 'active', 8, 'VIP');

-- --------------------------------------------------------
-- Table: orders (Customer orders)
-- --------------------------------------------------------
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_id` int(11) NOT NULL,
  `table_number` varchar(20) NOT NULL,
  `items_json` JSON NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL,
  `mandatory_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('new','preparing','ready','completed','cancelled') NOT NULL DEFAULT 'new',
  `notes` text,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `table_id` (`table_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: order_items (Individual items in orders)
-- --------------------------------------------------------
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `addons_json` JSON DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: analytics (Daily analytics summary)
-- --------------------------------------------------------
CREATE TABLE `analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `total_orders` int(11) NOT NULL DEFAULT 0,
  `total_revenue` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_customers` int(11) NOT NULL DEFAULT 0,
  `avg_order_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `top_product_id` int(11) DEFAULT NULL,
  `top_product_sales` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Sample Orders (for testing)
-- --------------------------------------------------------
INSERT INTO `orders` (`table_id`, `table_number`, `items_json`, `subtotal`, `tax_amount`, `mandatory_total`, `total_amount`, `status`, `created_at`) VALUES
(1, '1', '[{"product_id":1,"name":"حمص بالزيت","quantity":2,"price":5.00,"subtotal":10.00},{"product_id":5,"name":"عصير برتقال","quantity":2,"price":4.00,"subtotal":8.00}]', 18.00, 1.80, 1.50, 21.30, 'completed', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(2, '2', '[{"product_id":3,"name":"كبسة لحم","quantity":1,"price":15.00,"subtotal":15.00},{"product_id":7,"name":"كنافة بالجبن","quantity":1,"price":7.00,"subtotal":7.00}]', 22.00, 2.20, 1.50, 25.70, 'preparing', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(3, '3', '[{"product_id":4,"name":"شاورما دجاج","quantity":3,"price":8.00,"subtotal":24.00}]', 24.00, 2.40, 1.50, 27.90, 'new', NOW());

COMMIT;
