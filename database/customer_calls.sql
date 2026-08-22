CREATE TABLE `customer_calls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(355) NOT NULL,
  `is_distributor` tinyint(1) NOT NULL DEFAULT 0,
  `is_lead` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `remark` text DEFAULT NULL,
  `added_by` int(11) NOT NULL,
  `added_by_name` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
