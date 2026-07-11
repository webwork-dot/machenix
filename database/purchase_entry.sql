CREATE TABLE `purchase_entry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(250) NOT NULL,
  `invoice_number` varchar(250) NOT NULL,
  `invoice_date` date NOT NULL,
  `invoice_amount` decimal(16,2) NOT NULL DEFAULT 0.00,
  `image` varchar(250) DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `added_date` datetime NOT NULL,
  `added_by_id` int(11) NOT NULL,
  `added_by_name` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
