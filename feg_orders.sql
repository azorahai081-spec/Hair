-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 20, 2025 at 11:04 AM
-- Server version: 8.0.31
-- PHP Version: 8.1.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `feg_orders`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_number` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_cost` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `order_date`, `customer_name`, `customer_address`, `customer_phone`, `product_name`, `shipping_location`, `shipping_cost`, `total_price`, `status`, `created_at`) VALUES
(1, '9527', '2025-09-25 07:01:27', 'Mohammad Ariful Islam', 'Chattogram', '+8801810346511', 'FEG Hair Growth Serum usa 50ml × 1', 'ঢাকার মধ্যে:', '70.00', '1560.00', 'Pending', '2025-09-25 01:01:27'),
(2, '5306', '2025-09-25 07:04:39', 'Mohammad Ariful Islam', 'Chattogram', '+8801810346511', 'FEG Hair Growth Serum usa 50ml × 1', 'ঢাকার মধ্যে:', '70.00', '1560.00', 'Pending', '2025-09-25 01:04:39'),
(3, '7319', '2025-09-25 07:07:04', 'Mohammad Ariful Islam', 'Chattogram', '+8801810346511', 'FEG Hair Growth Serum usa 50ml × 1', 'ঢাকার মধ্যে:', '70.00', '1560.00', 'Pending', '2025-09-25 01:07:04'),
(4, '3171', '2025-09-25 07:07:31', 'Mohammad Ariful Islam', 'Chattogram', '+8801810346511', 'FEG Hair Growth Serum usa 50ml × 1', 'ঢাকার বাইরে:', '120.00', '1610.00', 'Pending', '2025-09-25 01:07:31'),
(5, '4050', '2025-09-25 07:25:33', 'Abdur Rahman', 'Chattogram', '+8801872-675240', 'FEG Hair Growth Serum usa 50ml × 1', 'ঢাকার বাইরে:', '120.00', '1610.00', 'Pending', '2025-09-25 01:25:33'),
(6, 'FEG-1006', '2025-09-25 07:30:44', 'Mohammad Ariful Islam', 'asd', '+8801810346511', 'FEG Hair Growth Serum usa 50ml × 1', 'ঢাকার বাইরে:', '120.00', '1610.00', 'Pending', '2025-09-25 01:30:44'),
(7, 'FEG-1007', '2025-09-25 07:31:41', 'Mohammad Ariful Islam', 'Chattogram', '+8801810346511', 'FEG Hair Growth Serum usa 50ml × 1', 'ঢাকার বাইরে:', '120.00', '1610.00', 'Pending', '2025-09-25 01:31:41'),
(8, 'FEG-1008', '2025-09-25 07:31:53', 'Mollah Mobasshir Royhan', 'Chattogram', '+8801872-675240', 'FEG Hair Growth Serum usa 50ml × 1', 'ঢাকার মধ্যে:', '70.00', '1560.00', 'Pending', '2025-09-25 01:31:53'),
(9, 'FEG-1009', '2025-09-25 07:50:34', 'Mohammad Ariful Islam', 'Chattogram', '+8801872-675240', 'FEG Hair Growth Serum usa 50ml × 1', 'ঢাকার বাইরে:', '120.00', '1610.00', 'Pending', '2025-09-25 01:50:34'),
(10, 'FEG-1010', '2025-09-25 08:00:59', 'Mollah Mobasshir Royhan', 'Dhaka', '+8801872-675240', 'FEG Hair Growth Serum usa 50ml × 1', 'ঢাকার বাইরে:', '120.00', '1610.00', 'Pending', '2025-09-25 02:00:59'),
(12, '202509-12', '0000-00-00 00:00:00', 'Mohammad Ariful Islam', 'Chattogram', '+8801810346511', 'FEG Hair Growth Serum usa 50ml × 1', 'ঢাকার মধ্যে:', '70.00', '1560.00', 'Pending', '2025-09-25 02:10:22'),
(14, '202509-14', '0000-00-00 00:00:00', 'Cines Valley', 'Dhaka', '+8801779664783', 'FEG Hair Growth Serum usa 50ml × 1', 'ঢাকার মধ্যে:', '70.00', '1560.00', 'Pending', '2025-09-25 08:15:58'),
(15, '202509-15', '0000-00-00 00:00:00', 'Abdur Rahman', 'Chattogram', '+8801810346511', 'FEG Hair Growth Serum usa 50ml × 1', 'ঢাকার বাইরে:', '120.00', '1610.00', 'Pending', '2025-09-25 08:19:59'),
(18, '202509-18', '2025-09-25 17:06:07', 'Mohammad Ariful Islam', 'Chattogram', '+8801810346511', 'FEG Hair Growth Serum usa 50ml × 1', 'ঢাকার মধ্যে:', '70.00', '1560.00', 'Pending', '2025-09-25 11:06:07'),
(19, '202509-19', '2025-09-25 17:21:49', 'Arif', 'Chittagong', '01820336015', 'FEG Hair Growth Serum usa 50ml × 1', ' ঢাকার মধ্যে:', '70.00', '1530.00', 'Pending', '2025-09-25 11:21:49'),
(22, '202510-22', '2025-10-20 16:43:43', 'Nazrul Islam', 'Dhaka', '01420336015', 'FEG Hair Growth Serum usa 50ml × 1', ' ঢাকার মধ্যে:', '70.00', '1530.00', 'Shipped', '2025-10-20 10:43:43'),
(23, '202510-23', '2025-10-20 16:45:43', 'Rakib', 'Chittagong', '01430336014', 'FEG Hair Growth Serum usa 50ml × 1', ' ঢাকার বাইরে:', '120.00', '1580.00', 'Delivered', '2025-10-20 10:45:43'),
(24, '202510-24', '2025-10-20 17:01:23', 'Sadia Jahan', 'Dhaka', '01820336015', 'FEG Hair Growth Serum usa 50ml × 1', ' ঢাকার বাইরে:', '120.00', '1580.00', 'Shipped', '2025-10-20 11:01:23');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` int NOT NULL,
  `review_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_initials` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'approved',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `name`, `date`, `rating`, `review_text`, `image_initials`, `status`, `is_featured`, `created_at`) VALUES
(1, 'Mollika Begum', 'October 15, 2025', 5, 'চুল পড়া বন্ধের জন্য সেরা একটি পণ্য। আমি ব্যবহার করে অনেক উপকার পেয়েছি। সবাইকে ব্যবহারের জন্য বলছি।', 'MB', 'approved', 0, '2025-10-20 10:21:20'),
(2, 'Arif Hasan', 'October 12, 2025', 5, 'অসাধারণ কাজ করে! মাত্র কয়েক সপ্তাহ ব্যবহারে আমার চুল পড়া কমে গেছে এবং নতুন চুল গজাচ্ছে। ধন্যবাদ।', 'AH', 'approved', 0, '2025-10-20 10:21:20'),
(3, 'Sadia Afrin', 'October 09, 2025', 4, 'প্রোডাক্টটি ভালো, তবে ডেলিভারি পেতে একটু দেরি হয়েছে। সার্বিকভাবে আমি সন্তুষ্ট।', 'SA', 'approved', 0, '2025-10-20 10:21:20'),
(4, 'Rakib Islam', 'October 20, 2025', 5, 'প্রোডাক্টটি ভালো', 'RI', 'approved', 0, '2025-10-20 10:58:14');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
