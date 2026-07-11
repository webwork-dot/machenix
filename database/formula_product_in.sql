-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 10, 2026 at 03:38 PM
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
-- Table structure for table `formula_product_in`
--

CREATE TABLE `formula_product_in` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `batch_no` varchar(255) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `total_off_cost` decimal(16,2) NOT NULL DEFAULT 0.00,
  `off_cost_pc` decimal(16,2) NOT NULL DEFAULT 0.00,
  `total_black_cost` decimal(16,2) NOT NULL DEFAULT 0.00,
  `black_cost_pc` decimal(16,2) NOT NULL DEFAULT 0.00,
  `total_actual_cost` decimal(16,2) NOT NULL DEFAULT 0.00,
  `actual_cost_pc` decimal(16,2) NOT NULL DEFAULT 0.00,
  `total_expense` decimal(16,2) NOT NULL DEFAULT 0.00,
  `final_total` decimal(16,2) NOT NULL DEFAULT 0.00,
  `added_by` int(11) DEFAULT NULL,
  `added_by_name` varchar(255) DEFAULT NULL,
  `added_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `formula_product_in`
--
ALTER TABLE `formula_product_in`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `formula_product_in`
--
ALTER TABLE `formula_product_in`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
