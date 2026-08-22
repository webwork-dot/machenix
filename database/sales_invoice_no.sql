CREATE TABLE `sales_invoice_no` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prefix` varchar(255) DEFAULT NULL,
  `year` varchar(255) DEFAULT NULL,
  `number` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
