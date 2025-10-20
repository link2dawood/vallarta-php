-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 03, 2025 at 05:25 PM
-- Server version: 10.11.14-MariaDB-deb11
-- PHP Version: 8.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dawood_anavitch`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `movie_id`, `name`, `price`, `image`, `quantity`) VALUES
(2, 2, 2, 'Sour Diesel', 320.00, 'sour_diesel.jpg', 1),
(3, 3, 4, 'Live Resin Cart - Wedding Cake', 450.00, 'wedding_cake_cart.jpg', 2),
(4, 4, 5, 'Glass Water Pipe - 12 inch', 800.00, 'glass_bong.jpg', 1),
(23, 1, 7, 'Test 1', 299.99, '763_IMG-20250823-WA0005.jpg', 3);

-- --------------------------------------------------------

--
-- Table structure for table `cat`
--

CREATE TABLE `cat` (
  `id` int(11) NOT NULL,
  `cat_name` varchar(255) NOT NULL,
  `parentOf` int(11) DEFAULT NULL,
  `added_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cat`
--

INSERT INTO `cat` (`id`, `cat_name`, `parentOf`, `added_by`) VALUES
(11, 'qwerty', 12, 1),
(12, 'Saadify', 13, 1),
(13, 'sadfasf', 2, 1),
(37, 'Eliana Craft', 13, 1);

-- --------------------------------------------------------

--
-- Table structure for table `grp`
--

CREATE TABLE `grp` (
  `id` int(11) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `parentOf` int(11) DEFAULT NULL,
  `added_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grp`
--

INSERT INTO `grp` (`id`, `group_name`, `parentOf`, `added_by`) VALUES
(8, 'Paula Travis', NULL, 1),
(10, 'Denise Miles', 8, 1),
(11, 'Nelle Lee', 12, 1),
(12, 'Cody Gilliam', 10, 1);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qnt_add` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`user_id`, `product_id`, `qnt_add`, `date`) VALUES
(1, 1, 30, '2024-08-15 03:00:00'),
(1, 2, 25, '2024-08-15 03:15:00'),
(2, 3, 60, '2024-08-16 04:00:00'),
(2, 4, 20, '2024-08-16 04:30:00'),
(3, 5, 15, '2024-08-17 05:00:00'),
(1, 1, 12, '2025-08-26 18:38:00'),
(1, 1, 1, '2025-08-26 18:46:23'),
(1, 1, 3, '2025-08-26 18:46:56'),
(1, 1, 3, '2025-08-26 18:47:39'),
(1, 4, 12, '2025-08-28 07:42:17');

-- --------------------------------------------------------

--
-- Table structure for table `movies`
--

CREATE TABLE `movies` (
  `movie_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `region_id` int(11) NOT NULL,
  `short_desc` text DEFAULT NULL,
  `long_desc` longtext DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `video_type` tinyint(4) DEFAULT 1,
  `video` text DEFAULT NULL,
  `trailer` varchar(255) DEFAULT NULL,
  `ad_img` varchar(255) DEFAULT NULL,
  `ad_link` varchar(255) DEFAULT NULL,
  `added_by` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `unit` int(11) DEFAULT 0,
  `featured` tinyint(4) DEFAULT 0,
  `pin_unpin_time` timestamp NULL DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`movie_id`, `title`, `cat_id`, `group_id`, `region_id`, `short_desc`, `long_desc`, `thumbnail`, `video_type`, `video`, `trailer`, `ad_img`, `ad_link`, `added_by`, `price`, `unit`, `featured`, `pin_unpin_time`, `date_created`) VALUES
(1, 'OG Kush Premium', 1, 1, 1, 'Classic Indica strain with earthy flavors', '<p>OG Kush is a legendary strain with a complex terpene profile. Perfect for evening relaxation with potent effects and distinctive pine aroma.</p>', '393_294_Snowflake strain sativa.png', 1, 'Qui cumque voluptatu', '', '', '', 1, 350.00, 41, 1, '2025-08-21 02:29:05', '2025-08-21 02:29:05'),
(2, 'Sour Diesel', 1, 2, 2, 'Energizing Sativa for daytime use', '<p>Sour Diesel delivers an energizing cerebral high perfect for creative activities. Known for its         \r\n  pungent diesel aroma and uplifting effects.</p>', 'sour_diesel.jpg', 1, '', '', '', '', 1, 320.00, 18, 0, NULL, '2025-08-21 02:29:05'),
(3, 'Brass battery', 5, 5, 4, 'battery', '<p>bateria</p>', '30_LOGO ITEC.png', 1, 'https://www.youtube.com/watch?v=TJAfLE39ZZ8&list=RDGMEMP-96bLtob-xyvCobnxVfyw&index=6', '', '398_ad_78_choco oreo RK.pngjpg', '', 2, 250.00, 15, 1, '2025-08-21 02:29:05', '2025-08-21 02:29:05'),
(4, 'Live Resin Cart - Wedding Cake', 3, 3, 4, '1g premium live resin cartridge', '<p>Wedding Cake live resin cartridge delivers exceptional flavor and potency. Made        \r\n  with fresh frozen cannabis for superior terpene retention.</p>', 'wedding_cake_cart.jpg', 1, '', '', '', '', 2, 450.00, 24, 0, NULL, '2025-08-21 02:29:05'),
(5, 'Glass Water Pipe - 12 inch', 5, 5, 5, 'Borosilicate glass bong', '<p>High-quality borosilicate glass water pipe with percolator for smooth hits. Includes bowl piece and easy-clean design.</p>', '898_218_Sour punch straws THC.png', 1, 'dfgsd', '', '', '', 3, 800.00, 6, 0, NULL, '2025-08-21 02:29:05'),
(7, 'Test 1 ', 1, 1, 1, 'Test 1 ', '<p>Test 1&nbsp;</p>\r\n<p>&nbsp;</p>\r\n<p>Test 1&nbsp;</p>\r\n<p>&nbsp;</p>\r\n<p>Test 1&nbsp;</p>', '763_IMG-20250823-WA0005.jpg', 1, 'https://youtu.be/zxbW0CCuT7E?si=SJ-Y0ZunxuUFZ056', 'https://youtu.be/zxbW0CCuT7E?si=SJ-Y0ZunxuUFZ056', '54_ad_maui wowie circle-1.png', 'Https://www.youtube.com', 1, 299.99, 48, 1, '2025-08-23 07:18:08', '2025-08-23 21:17:47');

-- --------------------------------------------------------

--
-- Table structure for table `ordere`
--

CREATE TABLE `ordere` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `number` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `method` varchar(100) NOT NULL,
  `adresse` text NOT NULL,
  `pin_code` varchar(50) DEFAULT NULL,
  `total_products` text NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `dat` timestamp NOT NULL DEFAULT current_timestamp(),
  `valid` varchar(50) DEFAULT 'pending',
  `valide_date` timestamp NULL DEFAULT NULL,
  `re_pro_date` timestamp NULL DEFAULT NULL,
  `confirm_date` timestamp NULL DEFAULT NULL,
  `rd_f_delv_date` timestamp NULL DEFAULT NULL,
  `in_delv_date` timestamp NULL DEFAULT NULL,
  `delivred_date` timestamp NULL DEFAULT NULL,
  `canceled_date` timestamp NULL DEFAULT NULL,
  `delayed_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ordere`
--

INSERT INTO `ordere` (`id`, `name`, `number`, `email`, `method`, `adresse`, `pin_code`, `total_products`, `total_price`, `dat`, `valid`, `valide_date`, `re_pro_date`, `confirm_date`, `rd_f_delv_date`, `in_delv_date`, `delivred_date`, `canceled_date`, `delayed_date`) VALUES
(1, 'John Smith', '+52 322 123 4567', 'john.smith@email.com', 'Cash on Delivery', 'Hotel Zone Norte, Puerto Vallarta, Jalisco', 'PV001', 'OG Kush Premium (2g), Cookies      \r\n  THC Gummies (1 pack)', 950.00, '2024-08-20 05:30:00', 'delivered', '2024-08-20 06:00:00', '2024-08-20 06:30:00', '2024-08-20 07:00:00', '2024-08-20 09:00:00', '2024-08-20 10:00:00', '2024-08-20 11:30:00', NULL, NULL),
(2, 'Sarah Johnson', '+52 322 234 5678', 'sarah.j@email.com', 'PayPal', 'Marina Vallarta, Puerto Vallarta, Jalisco', 'PV002', 'Sour Diesel (1g)', 320.00, '2024-08-20 09:15:00', 'in_delivery', '2024-08-20 09:30:00', '2024-08-20 10:00:00', '2024-08-20 10:30:00', '2024-08-20 12:00:00', '2024-08-20 12:30:00', NULL, NULL, NULL),
(3, 'Mike Wilson', '+52 322 345 6789', 'mike.w@email.com', 'Bank Transfer', 'Nuevo Vallarta, Nayarit', 'PV003', 'Live Resin Cart - Wedding Cake (2 carts)', 900.00, '2024-08-21 04:00:00', 'confirmed', '2024-08-21 04:30:00', '2024-08-21 05:00:00', '2024-08-21 05:30:00', NULL, NULL, NULL, NULL, NULL),
(4, 'Lisa Brown', '+52 322 456 7890', 'lisa.brown@email.com', 'Cash on Delivery', 'Centro Puerto Vallarta, Jalisco', 'PV004', 'Glass Water Pipe - 12 inch (1 piece)', 800.00, '2024-08-21 06:45:00', 'processing', '2024-08-21 07:00:00', '2024-08-21 07:30:00', NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'David Davis', '+52 322 567 8901', 'david.d@email.com', 'Stripe', 'Zona Hotelera Sur, Puerto Vallarta, Jalisco', 'PV005', 'OG Kush Premium (1g), Cookies THC Gummies     \r\n   (2 packs)', 850.00, '2024-08-21 11:20:00', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'Jason Gonzales', '942', 'gemysilij@mailinator.com', 'Visa MasterCard Via Stripe', 'Quia aut consequatur', 'Dolore nihil id et m', 'OG Kush Premium (2) , Cookies THC Gummies (1) ', 950.00, '2025-08-20 23:34:42', 'Recieved/Processing', NULL, '2025-08-25 22:00:00', NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'Robin Townsend', '517', 'myzokoco@mailinator.com', 'Cash', 'Modi in eum laboris ', 'Quo magnam omnis rer', 'Cookies THC Gummies (1) , Glass Water Pipe - 12 inch (1) ', 1050.00, '2025-08-22 03:27:54', 'Confirming', NULL, NULL, '2025-08-25 22:00:00', NULL, NULL, NULL, NULL, NULL),
(8, 'Dez', '55555558558', 'dez.slusher@gmail.com', 'Cash', 'Yvfg de Distrito Esp 5', 'Tf', 'Test 1  (1) , Glass Water Pipe - 12 inch (1) ', 1099.00, '2025-08-23 19:50:04', 'NEW!!! (Validate)', '2025-08-25 22:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'ninja warrior', '3340102643', 'mariel.navapez@gmail.com', 'Cash', 'Nuevo vallarta', '2354785', 'Chocolate Chunk Brownie (3) , OG Kush Premium (1) ', 1550.00, '2025-08-26 23:31:37', 'Ready for Delivery', NULL, NULL, NULL, '2025-08-26 22:00:00', NULL, NULL, NULL, NULL),
(10, 'Oliver Schwartz', '910', 'fycef@mailinator.com', 'Visa MasterCard Via Stripe', 'Eu voluptatem dolore', 'Et cupiditate quibus', 'Test 1 (1) ', 299.00, '2025-08-27 11:58:17', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reg`
--

CREATE TABLE `reg` (
  `id` int(11) NOT NULL,
  `region_name` varchar(255) NOT NULL,
  `parentOf` int(11) DEFAULT NULL,
  `dfee` varchar(255) DEFAULT NULL,
  `added_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reg`
--

INSERT INTO `reg` (`id`, `region_name`, `parentOf`, `dfee`, `added_by`) VALUES
(7, 'Dalton', NULL, '56', 1),
(9, 'Brielle', 7, '12', 1),
(13, 'Sloane', 7, '1000', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(32) NOT NULL,
  `name` varchar(255) NOT NULL,
  `admin?` enum('yes','no') DEFAULT 'no',
  `added_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `name`, `admin?`, `added_by`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'System Administrator', 'yes', NULL),
(12, 'sdasd', 'f3ed11bbdb94fd9ebdefbaf646ab94d3', 'sadasd', 'yes', 1),
(13, 'xejeralu', 'f3ed11bbdb94fd9ebdefbaf646ab94d3', 'Omar Bradshaw', 'no', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_info`
--

CREATE TABLE `user_info` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(32) NOT NULL,
  `role` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_info`
--

INSERT INTO `user_info` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'John Smith', 'staff@email.com', '25d55ad283aa400af464c76d713c07ad', 0),
(2, 'Sarah Johnson', 'sarah.j@email.com', '57e8814dba57360e6e5969c29e4eb68e', 0),
(3, 'Mike Wilson', 'mike.w@email.com', 'f66c92db31590e972ef005c433869b29', 0),
(4, 'Lisa Brown', 'lisa.brown@email.com', 'e69ed8b51937093a0ca27559f550d373', 0),
(5, 'David Davis', 'david.d@email.com', 'd6fb2e1ac1ce8affe7ae4cb8d83ffaac', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movie_id` (`movie_id`),
  ADD KEY `cart_ibfk_1` (`user_id`);

--
-- Indexes for table `cat`
--
ALTER TABLE `cat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `added_by` (`added_by`),
  ADD KEY `parentOf` (`parentOf`);

--
-- Indexes for table `grp`
--
ALTER TABLE `grp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `added_by` (`added_by`),
  ADD KEY `parentOf` (`parentOf`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`movie_id`),
  ADD KEY `cat_id` (`cat_id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `region_id` (`region_id`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `ordere`
--
ALTER TABLE `ordere`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reg`
--
ALTER TABLE `reg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `added_by` (`added_by`),
  ADD KEY `parentOf` (`parentOf`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `user_info`
--
ALTER TABLE `user_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `cat`
--
ALTER TABLE `cat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `grp`
--
ALTER TABLE `grp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `movie_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ordere`
--
ALTER TABLE `ordere`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reg`
--
ALTER TABLE `reg`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `user_info`
--
ALTER TABLE `user_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_info` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`) ON DELETE CASCADE;

--
-- Constraints for table `cat`
--
ALTER TABLE `cat`
  ADD CONSTRAINT `cat_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cat_ibfk_2` FOREIGN KEY (`parentOf`) REFERENCES `cat` (`id`);

--
-- Constraints for table `grp`
--
ALTER TABLE `grp`
  ADD CONSTRAINT `grp_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `grp_ibfk_2` FOREIGN KEY (`parentOf`) REFERENCES `grp` (`id`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_info` (`id`),
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `movies` (`movie_id`);

--
-- Constraints for table `movies`
--
ALTER TABLE `movies`
  ADD CONSTRAINT `movies_ibfk_1` FOREIGN KEY (`cat_id`) REFERENCES `cat` (`id`),
  ADD CONSTRAINT `movies_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `grp` (`id`),
  ADD CONSTRAINT `movies_ibfk_3` FOREIGN KEY (`region_id`) REFERENCES `reg` (`id`),
  ADD CONSTRAINT `movies_ibfk_4` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `reg`
--
ALTER TABLE `reg`
  ADD CONSTRAINT `reg_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reg_ibfk_2` FOREIGN KEY (`parentOf`) REFERENCES `reg` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
