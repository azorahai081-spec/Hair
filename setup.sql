-- This script creates the 'orders' table required for the project.
-- You can run this in your database management tool (like phpMyAdmin)
-- to set up the database structure.

-- Set character set and collation for full Unicode support
SET NAMES utf8mb4;

-- Create the 'orders' table
CREATE TABLE `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR(10) NOT NULL,
  `order_date` DATETIME NOT NULL,
  `customer_name` VARCHAR(255) NOT NULL,
  `customer_address` TEXT NOT NULL,
  `customer_phone` VARCHAR(50) NOT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `shipping_location` VARCHAR(255) NOT NULL,
  `shipping_cost` DECIMAL(10,2) NOT NULL,
  `total_price` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
