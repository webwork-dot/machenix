-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 01, 2026 at 10:31 AM
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
-- Table structure for table `sales_order_product_batch`
--

CREATE TABLE `sales_order_product_batch` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_product_id` int(11) NOT NULL,
  `batch_no` varchar(250) DEFAULT NULL,
  `batch_qty` int(11) NOT NULL,
  `avail_white_qty` int(11) NOT NULL DEFAULT 0,
  `avail_black_qty` int(11) NOT NULL DEFAULT 0,
  `qty` int(11) NOT NULL DEFAULT 0,
  `white_qty` int(11) NOT NULL DEFAULT 0,
  `black_qty` int(11) NOT NULL DEFAULT 0,
  `recieved_qty` int(11) NOT NULL DEFAULT 0,
  `recieved_black_qty` int(11) NOT NULL DEFAULT 0,
  `return_qty` int(11) NOT NULL DEFAULT 0,
  `return_black_qty` int(11) NOT NULL DEFAULT 0,
  `amount` decimal(16,2) NOT NULL DEFAULT 0.00,
  `bill_amount` decimal(16,2) NOT NULL DEFAULT 0.00,
  `bill_total` decimal(16,2) NOT NULL DEFAULT 0.00,
  `gst` decimal(16,2) NOT NULL DEFAULT 0.00,
  `gst_amount` decimal(16,2) NOT NULL DEFAULT 0.00,
  `total_bill_gst_amount` decimal(16,2) NOT NULL DEFAULT 0.00,
  `black_amount` decimal(16,2) NOT NULL DEFAULT 0.00,
  `black_total` decimal(16,2) NOT NULL DEFAULT 0.00,
  `final_total` decimal(16,2) NOT NULL DEFAULT 0.00,
  `added_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sales_order_product_batch`
--
ALTER TABLE `sales_order_product_batch`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sales_order_product_batch`
--
ALTER TABLE `sales_order_product_batch`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
