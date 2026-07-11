CREATE TABLE `damage_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `method` varchar(50) DEFAULT NULL,
  `excel_id` varchar(250) DEFAULT NULL,
  `warehouse_id` int(11) NOT NULL,
  `warehouse_name` varchar(250) NOT NULL,
  `company_id` int(11) NOT NULL,
  `company_name` varchar(250) NOT NULL,
  `reference_no` varchar(250) NOT NULL,
  `date` date NOT NULL,
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(250) NOT NULL,
  `reason` longtext DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `added_date` datetime NOT NULL,
  `added_by_id` int(11) NOT NULL,
  `added_by_name` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
