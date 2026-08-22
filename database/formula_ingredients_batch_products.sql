CREATE TABLE `formula_ingredients_batch_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `batch_no` varchar(255) NOT NULL,
  `white_qty` int(11) NOT NULL DEFAULT 0,
  `black_qty` int(11) NOT NULL DEFAULT 0,
  `total_qty` int(11) NOT NULL DEFAULT 0,
  `off_cost` decimal(16,2) NOT NULL DEFAULT 0.00,
  `total_off_cost` decimal(16,2) NOT NULL DEFAULT 0.00,
  `black_cost` decimal(16,2) NOT NULL DEFAULT 0.00,
  `total_black_cost` decimal(16,2) NOT NULL DEFAULT 0.00,
  `actual_cost` decimal(16,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
