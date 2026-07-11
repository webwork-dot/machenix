CREATE TABLE `excel_po_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unique_id` varchar(250) DEFAULT NULL,
  `is_move` tinyint(1) NOT NULL DEFAULT 0,
  `is_complete` tinyint(1) NOT NULL DEFAULT 0,
  `product_name` varchar(250) DEFAULT NULL,
  `rate` decimal(16,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `cartoon` int(11) DEFAULT NULL,
  `gst_percentage` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
