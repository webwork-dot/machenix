-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 27, 2026 at 10:24 AM
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
-- Table structure for table `sales_order`
--

CREATE TABLE `sales_order` (
  `id` int(11) NOT NULL,
  `type` enum('normal','bill','conversion') NOT NULL DEFAULT 'normal',
  `unique_id` varchar(250) DEFAULT NULL,
  `order_type` enum('normal','excel','') NOT NULL DEFAULT 'normal',
  `order_no` varchar(250) NOT NULL,
  `invoice_no` varchar(250) NOT NULL,
  `invoice_date` date NOT NULL,
  `refrence_no` varchar(250) DEFAULT NULL,
  `date` date NOT NULL,
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(250) NOT NULL,
  `shipping_state_id` int(11) DEFAULT NULL,
  `shipping_state_name` varchar(255) DEFAULT NULL,
  `shipping_city_id` int(11) DEFAULT NULL,
  `shipping_city_name` varchar(255) DEFAULT NULL,
  `shipping_pincode` varchar(11) DEFAULT NULL,
  `shipping_gst` varchar(255) DEFAULT NULL,
  `shipping_gst_no` varchar(255) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `billing_state_id` int(11) DEFAULT NULL,
  `billing_state_name` varchar(255) DEFAULT NULL,
  `billing_city_id` int(11) DEFAULT NULL,
  `billing_city_name` varchar(255) DEFAULT NULL,
  `billing_pincode` int(11) DEFAULT NULL,
  `billing_gst` varchar(255) DEFAULT NULL,
  `billing_gst_no` varchar(255) DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `warehouse_id` int(11) NOT NULL,
  `warehouse_name` varchar(250) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `company_name` varchar(250) DEFAULT NULL,
  `narration` mediumtext DEFAULT NULL,
  `remark` mediumtext DEFAULT NULL,
  `basic_value` decimal(16,2) NOT NULL DEFAULT 0.00,
  `net_sales_value_1` decimal(16,2) NOT NULL DEFAULT 0.00,
  `total_black_amt` decimal(16,2) NOT NULL DEFAULT 0.00,
  `gst_type` varchar(250) DEFAULT NULL,
  `cgst_per` int(11) NOT NULL,
  `sgst_per` int(11) NOT NULL,
  `igst_per` int(11) NOT NULL,
  `central_gst` decimal(16,2) NOT NULL DEFAULT 0.00,
  `state_gst` decimal(16,2) NOT NULL DEFAULT 0.00,
  `igst` decimal(16,2) NOT NULL DEFAULT 0.00,
  `gst_total` decimal(16,2) NOT NULL DEFAULT 0.00,
  `net_sales_value_2` decimal(16,2) NOT NULL DEFAULT 0.00,
  `other_charges_name` varchar(250) DEFAULT NULL,
  `other_charges_amount` decimal(16,2) NOT NULL DEFAULT 0.00,
  `round_of` varchar(250) DEFAULT '0',
  `grand_total` decimal(16,2) NOT NULL DEFAULT 0.00,
  `total_white_paid` decimal(16,2) NOT NULL DEFAULT 0.00,
  `total_black_paid` decimal(16,2) NOT NULL DEFAULT 0.00,
  `is_generated` tinyint(1) NOT NULL DEFAULT 0,
  `is_weird` tinyint(1) NOT NULL DEFAULT 0,
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `added_by_id` int(11) NOT NULL,
  `added_by_name` varchar(250) NOT NULL,
  `added_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sales_order`
--
ALTER TABLE `sales_order`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sales_order`
--
ALTER TABLE `sales_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
