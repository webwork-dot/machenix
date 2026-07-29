-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 22, 2026 at 12:00 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `machenix_dbc`
--

-- --------------------------------------------------------

--
-- Table structure for table `replace_products`
--

CREATE TABLE `replace_products` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_prod_id` int(11) NOT NULL,
  `prev_id` int(11) DEFAULT NULL,
  `po_id` int(11) DEFAULT NULL,
  `type` enum('pending','po','loading','received') NOT NULL DEFAULT 'pending',
  `product_id` int(11) NOT NULL,
  `product_name` varchar(355) DEFAULT NULL,
  `item_code` varchar(355) DEFAULT NULL,
  `qty` int(11) NOT NULL DEFAULT 0,
  `added_by` int(11) DEFAULT NULL,
  `is_deleted` TINYINT(1) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `replace_products`
--
ALTER TABLE `replace_products`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `replace_products`
--
ALTER TABLE `replace_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
