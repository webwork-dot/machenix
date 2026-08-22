CREATE TABLE `staff_commission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `commission_id` int(11) NOT NULL,
  `profit_id` int(11) NOT NULL DEFAULT 0,
  `customer_comm` decimal(16,2) NOT NULL DEFAULT 0.00,
  `distributer_comm` decimal(16,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
