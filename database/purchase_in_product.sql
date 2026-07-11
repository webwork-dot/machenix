-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2026 at 03:09 PM
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
-- Table structure for table `purchase_in_product`
--

CREATE TABLE `purchase_in_product` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `product_type` enum('ready','spare','') NOT NULL DEFAULT '',
  `product_id` int(11) NOT NULL,
  `categories` varchar(255) DEFAULT NULL,
  `sizes` varchar(255) DEFAULT NULL,
  `group_id` varchar(255) DEFAULT NULL,
  `color_id` int(11) DEFAULT NULL,
  `color_name` varchar(255) DEFAULT NULL,
  `product_name` varchar(250) NOT NULL,
  `hsn_code` varchar(250) DEFAULT NULL,
  `item_code` varchar(250) DEFAULT NULL,
  `unit` varchar(20) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `loading_qty` int(11) NOT NULL DEFAULT 0,
  `actual_qty` int(11) NOT NULL DEFAULT 0,
  `actual_rmb` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `total_rmb` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `official_ci_qty` int(11) NOT NULL DEFAULT 0,
  `black_qty` int(11) NOT NULL DEFAULT 0,
  `unit_price_rmb` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `actual_usd` decimal(15,2) DEFAULT 0.00,
  `actual_inr` decimal(15,2) DEFAULT 0.00,
  `total_amount_rmb` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `official_ci_unit_price_usd` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `total_amount_usd` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `official_rate_rs` decimal(15,2) NOT NULL DEFAULT 0.00,
  `duty_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `duty_amt` decimal(15,2) NOT NULL DEFAULT 0.00,
  `duty_surcharge` decimal(15,2) NOT NULL DEFAULT 0.00,
  `taxable_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `gst_amt` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amt` decimal(15,2) NOT NULL DEFAULT 0.00,
  `official_total_rs` decimal(15,2) NOT NULL DEFAULT 0.00,
  `black_total_price` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `pkg_ctn` int(11) NOT NULL DEFAULT 0,
  `nw_kg` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `total_nw_kg` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `gw_kg` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `total_gw_kg` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `length` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `width` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `height` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `total_cbm_value` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `cbm` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `total_cbm` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `pending_po_qty` int(11) NOT NULL DEFAULT 0,
  `loading_list_qty` int(11) NOT NULL DEFAULT 0,
  `in_stock_qty` int(11) NOT NULL DEFAULT 0,
  `current_company_qty` int(11) NOT NULL DEFAULT 0,
  `cartoon` int(11) NOT NULL,
  `rate` decimal(16,5) NOT NULL,
  `basic_amount` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `discount` int(11) DEFAULT 0,
  `discount_amount` decimal(16,5) DEFAULT 0.00000,
  `gst` int(11) NOT NULL DEFAULT 0,
  `gst_amount` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `total_val` decimal(16,5) NOT NULL,
  `pending` int(11) DEFAULT 0,
  `received` int(11) DEFAULT 0,
  `received_date` date DEFAULT NULL,
  `invoice_no` int(11) DEFAULT 0,
  `invoice_supplier_id` int(11) NOT NULL DEFAULT 0,
  `invoice` varchar(255) DEFAULT NULL,
  `invoice_date` datetime DEFAULT NULL,
  `invoice_terms` text DEFAULT NULL,
  `invoice_price_terms` text DEFAULT NULL,
  `is_priority` tinyint(1) NOT NULL DEFAULT 0,
  `is_complete` tinyint(1) NOT NULL DEFAULT 0,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `sort` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `purchase_in_product`
--
ALTER TABLE `purchase_in_product`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `purchase_in_product`
--
ALTER TABLE `purchase_in_product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
