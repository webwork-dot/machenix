-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 06, 2026 at 12:11 PM
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
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) NOT NULL,
  `warehouse_name` varchar(250) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(250) NOT NULL,
  `categories` varchar(255) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `item_code` varchar(250) DEFAULT NULL,
  `rack_id` int(11) DEFAULT NULL,
  `rack_name` varchar(355) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `room_name` varchar(355) DEFAULT NULL,
  `draw_id` int(11) DEFAULT NULL,
  `draw_name` varchar(355) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `actual_rmb` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `total_rmb` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `actual_usd` decimal(16,5) DEFAULT 0.00000,
  `official_qty` int(11) NOT NULL DEFAULT 0,
  `official_rate_rs` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `official_total_rs` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `actual_inr` decimal(16,5) DEFAULT 0.00000,
  `black_qty` int(11) NOT NULL DEFAULT 0,
  `pending_qty` int(11) NOT NULL DEFAULT 0,
  `duty_percent` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `duty_amt` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `duty_surcharge` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `taxable_value` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `gst_amt` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `total_amt` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `expense` decimal(16,5) NOT NULL DEFAULT 0.00000 COMMENT 'actual exp per product',
  `total_expense` decimal(16,5) NOT NULL DEFAULT 0.00000 COMMENT 'total expense on actual stock (actual qty * actual inr + duty + surge + gst)',
  `official_expense` decimal(16,5) NOT NULL DEFAULT 0.00000 COMMENT 'official exp per product',
  `total_official_expense` decimal(16,5) NOT NULL DEFAULT 0.00000 COMMENT 'total official exp on official stock (off qty * off inr + duty + surge + gst)',
  `official_exp_per_pc` decimal(16,5) NOT NULL DEFAULT 0.00000 COMMENT 'total_official_expense / off qty',
  `actual_cost_with_exp` decimal(16,5) NOT NULL DEFAULT 0.00000 COMMENT 'total_expense / act qty',
  `batch_no` varchar(250) DEFAULT NULL,
  `barcode_no` varchar(255) DEFAULT NULL,
  `barcode_url` varchar(255) DEFAULT NULL,
  `po_row_id` int(11) DEFAULT 0,
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
