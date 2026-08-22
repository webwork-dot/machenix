CREATE TABLE `po_expense_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `charges_id` int(11) DEFAULT NULL,
  `expense_name` varchar(255) NOT NULL,
  `rmb` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `usd` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `amount` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `gst` decimal(16,2) NOT NULL DEFAULT 0.00,
  `gst_amt` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `total_amt` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
