-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 19, 2025 at 06:11 AM
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
-- Database: `agritrade`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `updated_at` timestamp(6) NULL DEFAULT current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logistics_assignments`
--

CREATE TABLE `logistics_assignments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `logistics_id` int(11) NOT NULL,
  `status` enum('assigned','picked_up','in_transit','delivered') NOT NULL DEFAULT 'assigned',
  `pickup_date` datetime DEFAULT NULL,
  `delivery_date` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logistics_orders`
--

CREATE TABLE `logistics_orders` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `logistics_id` int(11) DEFAULT NULL,
  `status` enum('available','assigned','picked_up','delivered') DEFAULT 'available',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `market_prices`
--

CREATE TABLE `market_prices` (
  `id` int(11) NOT NULL,
  `updated_by` varchar(10) NOT NULL,
  `product_name` varchar(20) NOT NULL,
  `category` varchar(11) NOT NULL,
  `price` int(10) NOT NULL,
  `crop_name` varchar(100) DEFAULT NULL,
  `price_per_kg` decimal(10,2) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `market_prices`
--

INSERT INTO `market_prices` (`id`, `updated_by`, `product_name`, `category`, `price`, `crop_name`, `price_per_kg`, `updated_at`) VALUES
(2, '1', 'Tomato', 'vegetables', 100, NULL, NULL, '2025-06-14 06:37:16'),
(3, '1', 'Tomato', 'grains', 60, NULL, NULL, '2025-06-14 06:37:31');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `buyer_id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 4, NULL, 'New order #11 received for your product(s).', 0, '2025-06-17 06:20:18'),
(2, 4, NULL, 'New order #11 received for your product(s).', 0, '2025-06-17 06:20:18'),
(3, 4, NULL, 'New order #11 received for your product(s).', 0, '2025-06-17 06:20:18'),
(4, 4, NULL, 'New order #11 received for your product(s).', 0, '2025-06-17 06:20:18'),
(5, 4, NULL, 'New order #12 received for your product(s).', 0, '2025-06-17 08:32:02'),
(6, 0, 5, 'Your Order #12 has been approved by the supplier. Please upload your payment.', 0, '2025-06-17 17:46:09'),
(7, 4, NULL, 'New order #13 received for your product(s).', 0, '2025-06-18 11:43:42'),
(8, 4, NULL, 'New order #14 received for your product(s).', 0, '2025-06-18 15:44:18'),
(9, 0, 5, 'Your Order #14 has been approved by the supplier. Please upload your payment.', 0, '2025-06-18 15:44:58'),
(10, 4, NULL, 'New order #15 received for your product(s).', 0, '2025-06-18 16:05:36'),
(11, 4, NULL, 'New order #18 received for your product(s).', 0, '2025-06-18 16:14:09'),
(12, 4, NULL, 'New order #19 received for your product(s).', 0, '2025-06-18 17:09:34'),
(13, 4, NULL, 'New order #20 received for your product(s).', 0, '2025-06-19 03:41:13');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `supplier_id` int(10) NOT NULL,
  `logistics_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `payment_slip` varchar(225) NOT NULL,
  `status` enum('pending','confirmed','shipped','delivered','cancelled','supplier_approved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivery_status` enum('Pending','Picked Up','Delivered') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `buyer_id`, `supplier_id`, `logistics_id`, `user_id`, `total_price`, `payment_slip`, `status`, `created_at`, `delivery_status`) VALUES
(11, 5, 0, 0, NULL, 668.00, '../uploads/slips/slip_68511c101b18e_Screenshot 2025-05-05 163134.png', '', '2025-06-17 06:20:18', 'Pending'),
(12, 5, 0, 0, NULL, 200.00, 'slip_1750149162_12.png', 'confirmed', '2025-06-17 08:32:02', 'Pending'),
(13, 5, 0, 0, NULL, 67.00, 'slip_1750247037_13.png', 'confirmed', '2025-06-18 11:43:42', 'Pending'),
(14, 5, 0, 0, NULL, 100.00, 'slip_1750261471_14.png', 'confirmed', '2025-06-18 15:44:18', 'Pending'),
(15, 5, 0, 0, NULL, 100.00, 'slip_1750262745_15.png', 'confirmed', '2025-06-18 16:05:36', 'Pending'),
(16, 1, 0, 0, NULL, 5000.00, '', 'confirmed', '2025-06-18 16:10:17', 'Pending'),
(17, 1, 0, 6, NULL, 4500.00, '', '', '2025-06-18 16:11:52', 'Pending'),
(18, 5, 0, 6, NULL, 100.00, 'slip_1750263258_18.png', '', '2025-06-18 16:14:09', 'Delivered'),
(19, 5, 0, 6, NULL, 100.00, 'slip_1750266584_19.png', '', '2025-06-18 17:09:34', 'Pending'),
(20, 5, 0, NULL, NULL, 67.00, 'slip_1750304487_20.png', 'pending', '2025-06-19 03:41:13', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(9, 11, 7, 4, 67.00),
(10, 11, 9, 1, 100.00),
(11, 11, 6, 2, 100.00),
(12, 11, 8, 1, 100.00),
(13, 12, 8, 2, 100.00),
(14, 13, 7, 1, 67.00),
(15, 14, 8, 1, 100.00),
(16, 15, 8, 1, 100.00),
(17, 18, 6, 1, 100.00),
(18, 19, 9, 1, 100.00),
(19, 20, 7, 1, 67.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `card_number` varchar(20) DEFAULT NULL,
  `card_holder` varchar(100) DEFAULT NULL,
  `expiry_date` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `method` varchar(100) NOT NULL,
  `payment_status` enum('pending','confirmed','rejected') DEFAULT 'pending',
  `reference` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `buyer_id`, `order_id`, `payment_method`, `card_number`, `card_holder`, `expiry_date`, `created_at`, `method`, `payment_status`, `reference`) VALUES
(9, 5, 11, NULL, NULL, NULL, NULL, '2025-06-17 06:20:18', 'bank transfer', 'rejected', '12345'),
(10, 5, 12, NULL, NULL, NULL, NULL, '2025-06-17 08:32:02', 'bank transfer', 'confirmed', '12346'),
(11, 5, 13, NULL, NULL, NULL, NULL, '2025-06-18 11:43:42', 'bank transfer', 'confirmed', '56677'),
(12, 5, 14, NULL, NULL, NULL, NULL, '2025-06-18 15:44:18', 'bank transfer', 'confirmed', '12346'),
(13, 5, 15, NULL, NULL, NULL, NULL, '2025-06-18 16:05:36', 'bank transfer', 'confirmed', '12345'),
(14, 5, 18, NULL, NULL, NULL, NULL, '2025-06-18 16:14:09', 'bank transfer', 'confirmed', '12387'),
(15, 5, 19, NULL, NULL, NULL, NULL, '2025-06-18 17:09:34', 'bank transfer', 'confirmed', '12345'),
(16, 5, 20, NULL, NULL, NULL, NULL, '2025-06-19 03:41:13', 'bank transfer', 'pending', '12345');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `supplier_id` int(10) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `featured` varchar(25) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `supplier_id`, `image_url`, `name`, `featured`, `category`, `price`, `description`, `quantity`, `image`, `created_by`, `created_at`) VALUES
(6, 4, 'uploads/products/1750053208_The Best Tomatoes To Grow For Making Juice.jpg', 'Red Tomatoes', '0', 'vegetable', 100.00, 'organic red tomatoes', 17, NULL, NULL, '2025-06-16 05:53:28'),
(7, 4, 'uploads/products/1750053512_The Nutritional Importance of Cumin Seeds in Our Diet.jpg', 'cumin', '1', 'seed', 67.00, 'test', 19, NULL, NULL, '2025-06-16 05:58:32'),
(8, 4, 'uploads/products/1750071609_How to Grow Potatoes in Containers _ Gardener’s Path.jpg', 'potatoes', '1', 'vegetable', 100.00, 'organic', 23, NULL, NULL, '2025-06-16 11:00:09'),
(9, 4, 'uploads/products/1750071658_When To Fertilize Plants_ Best Times For Application Of Fertilizer.jpg', 'yara mila', '1', 'fertilizer', 100.00, 'soil', 26, NULL, NULL, '2025-06-16 11:00:58');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `platform_name` varchar(255) DEFAULT NULL,
  `commission_rate` float DEFAULT NULL,
  `support_email` varchar(255) DEFAULT NULL,
  `min_password_length` int(11) DEFAULT NULL,
  `session_timeout` int(11) DEFAULT NULL,
  `max_login_attempts` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `platform_name`, `commission_rate`, `support_email`, `min_password_length`, `session_timeout`, `max_login_attempts`) VALUES
(1, 'AgriTrade', 5, 'support@agritrade.com', 8, 30, 6);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_info` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'farmer',
  `full_name` varchar(20) NOT NULL,
  `phone` int(10) NOT NULL,
  `address` varchar(225) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_blocked` varchar(10) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `full_name`, `phone`, `address`, `created_at`, `is_blocked`, `status`) VALUES
(1, 'admin', 'admin@agritrade.com', '$2y$10$Vl0B2508a7xk33YL5jKYt.k/Nzq43ztZUUQ.cIVYHHOggv2jmT2J6', 'admin', 'System Administrator', 0, '0', '2025-06-14 06:11:27', '', ''),
(3, 'ruvini', 'neth98mi@gmail.com', '$2y$10$wvwZps7/ZRxNhBguy0wxSO4C6g1g4p9zuJM0DtuWSrrqfcV6KIegm', '', 'Nethmini Jayathilaka', 763415083, '7', '2025-06-14 13:35:23', '', ''),
(4, 'NethmiJay', 'neth98@gmail.com', '$2y$10$tyM/LEl7AmW9fO6yhOE5cOouYBZ8qW/43oQSAhgqCAP3tXRMcG16C', 'supplier', 'Nethmini Jayathilaka', 763415083, '7/13 Sarasavi Road, Sarasavi mawatha', '2025-06-14 13:41:49', '', ''),
(5, 'riri', 'nethminijayathilaka09@gmail.com', '$2y$10$TUxOHCI32vVUAsouLx93eOTQtgSdmZQE6LccM5WpnQlv/lCkSH8wy', 'buyer', 'Nethmini Jayathilaka', 763415086, '7/13 Sarasavi Road, Sarasavi mawatha', '2025-06-14 15:45:40', '', ''),
(6, 'cici', 'gy@gmail.com', '$2y$10$gH2Hj8ruZon1fDrBNXjg1.msOHa6lLGQYnoPa4P3dJHNf08gq7ns6', 'logistics', 'Nethmini Jayathilaka', 763415083, '7/13 Sarasavi Road, Sarasavi mawatha', '2025-06-15 07:41:01', '', ''),
(7, 'ruivini', 'ru@gmail.com', '$2y$10$uRKv.nhrs76tdK1zH94wuer2nQUGJSdiDAHGyNMwB66skUOx4T5ty', 'supplier', 'ruvini maha', 762342341, 'colombo', '2025-06-19 03:36:48', '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `logistics_assignments`
--
ALTER TABLE `logistics_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `logistics_id` (`logistics_id`);

--
-- Indexes for table `logistics_orders`
--
ALTER TABLE `logistics_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `logistics_id` (`logistics_id`);

--
-- Indexes for table `market_prices`
--
ALTER TABLE `market_prices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `logistics_assignments`
--
ALTER TABLE `logistics_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logistics_orders`
--
ALTER TABLE `logistics_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `market_prices`
--
ALTER TABLE `market_prices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `logistics_assignments`
--
ALTER TABLE `logistics_assignments`
  ADD CONSTRAINT `logistics_assignments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `logistics_assignments_ibfk_2` FOREIGN KEY (`logistics_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `logistics_orders`
--
ALTER TABLE `logistics_orders`
  ADD CONSTRAINT `logistics_orders_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `logistics_orders_ibfk_2` FOREIGN KEY (`logistics_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
