CREATE TABLE `replace_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
