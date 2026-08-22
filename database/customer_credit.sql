CREATE TABLE `customer_credit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `item_no` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `credit_balance` decimal(16,2) NOT NULL DEFAULT 0.00,
  `debit_balance` decimal(16,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
