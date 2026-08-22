CREATE TABLE `goods_return_charges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `gst` decimal(16,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(16,2) NOT NULL DEFAULT 0.00,
  `total_amt` decimal(16,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
