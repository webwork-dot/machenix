-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 09, 2026 at 08:17 AM
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
-- Table structure for table `invoice_order_products`
--

CREATE TABLE `invoice_order_products` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(250) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 0,
  `item_code` varchar(250) NOT NULL,
  `amount` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `total_amount` decimal(16,2) NOT NULL,
  `bill_amount` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `total_bill_gst_amount` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `gst` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `gst_amount` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `bill_total` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `final_total` decimal(16,5) NOT NULL DEFAULT 0.00000
  `return_qty` int(11) NOT NULL DEFAULT 0,
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `invoice_order_products`
--
ALTER TABLE `invoice_order_products`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `invoice_order_products`
--
ALTER TABLE `invoice_order_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
