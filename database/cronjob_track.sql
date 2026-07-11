CREATE TABLE `cronjob_track` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50) DEFAULT NULL,
  `order_slot` varchar(150) DEFAULT NULL,
  `function_name` varchar(500) NOT NULL,
  `json_request` longtext DEFAULT NULL,
  `json_data` longtext DEFAULT NULL,
  `remark` text DEFAULT NULL,
  `created_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
