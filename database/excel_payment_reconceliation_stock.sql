CREATE TABLE `excel_payment_reconceliation_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(250) DEFAULT NULL,
  `unique_id` varchar(250) NOT NULL,
  `is_move` tinyint(1) NOT NULL DEFAULT 0,
  `is_complete` tinyint(1) NOT NULL DEFAULT 0,
  `product` varchar(250) DEFAULT NULL,
  `batch_no` varchar(250) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `amount` decimal(16,2) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
