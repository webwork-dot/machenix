-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 17, 2026 at 11:53 AM
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
-- Table structure for table `sales_commission`
--

CREATE TABLE `sales_commission` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_product_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `commission_id` int(11) NOT NULL,
  `product_comm` decimal(16,2) NOT NULL DEFAULT 0.00,
  `product_comm_amt` decimal(16,2) NOT NULL DEFAULT 0.00,
  `staff_id` int(11) NOT NULL,
  `staff_comm_id` int(11) NOT NULL,
  `customer_comm` decimal(16,2) NOT NULL DEFAULT 0.00,
  `distributer_comm` decimal(16,2) NOT NULL DEFAULT 0.00,
  `commission_amount` decimal(16,2) NOT NULL DEFAULT 0.00,
  `share_staff_id` int(11) NOT NULL DEFAULT 0,
  `shared_commission` decimal(16,2) NOT NULL DEFAULT 0.00,
  `my_commission` decimal(16,2) NOT NULL DEFAULT 0.00,
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `payment_date` DATE DEFAULT NULL,
  `remark` TEXT DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `sales_commission` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_product_id` int(11) NOT NULL,
  `order_product_batch_id` int(11) NOT NULL, new

  `product_id` int(11) NOT NULL,
  `commission_id` int(11) NOT NULL,
  `product_comm` decimal(16,2) NOT NULL DEFAULT 0.00,
  `product_comm_amt` decimal(16,2) NOT NULL DEFAULT 0.00,

  `staff_id` int(11) NOT NULL,
  `staff_comm_id` int(11) NOT NULL,
  `customer_range` varchar(255) DEFAULT NULL, new
  `customer_comm` decimal(16,2) NOT NULL DEFAULT 0.00,
  `distributer_comm` decimal(16,2) NOT NULL DEFAULT 0.00,
  `commission_amount` decimal(16,2) NOT NULL DEFAULT 0.00,

  `share_staff_id` int(11) NOT NULL DEFAULT 0,
  `shared_staff_comm_id` int(11) NOT NULL DEFAULT 0, new
  `shared_commission` decimal(16,2) NOT NULL DEFAULT 0.00,
  `my_commission` decimal(16,2) NOT NULL DEFAULT 0.00,

  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `payment_date` DATE DEFAULT NULL,
  `remark` TEXT DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Indexes for dumped tables
--

--
-- Indexes for table `sales_commission`
--
ALTER TABLE `sales_commission`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sales_commission`
--
ALTER TABLE `sales_commission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
