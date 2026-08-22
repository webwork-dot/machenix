CREATE TABLE `customer_payment_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_id` int(11) NOT NULL DEFAULT 0,
  `order_id` varchar(255) DEFAULT NULL,
  `order_date` varchar(255) DEFAULT NULL,
  `refrence_no` varchar(255) DEFAULT NULL,
  `order_total` varchar(255) DEFAULT NULL,
  `order_paid` varchar(255) DEFAULT NULL,
  `order_pending` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `added_date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
