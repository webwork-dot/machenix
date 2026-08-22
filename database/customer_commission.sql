CREATE TABLE `customer_commission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `shared_staff_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `commission_id` int(11) NOT NULL,
  `profit_id` int(11) NOT NULL,
  `my_commission` decimal(16,2) NOT NULL DEFAULT 0.00,
  `shared_commission` decimal(16,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
