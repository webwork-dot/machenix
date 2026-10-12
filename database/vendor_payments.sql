CREATE TABLE `vendor_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `vendor_name` varchar(255) NOT NULL,
  `invoice_no` varchar(255) NOT NULL,
  `usd` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `rmb` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `inr` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `payment_type` enum('official','unofficial','') NOT NULL,
  `payment_method` ENUM('cash', 'cheque','') NOT NULL DEFAULT '',
  `company_bank` int(11) NOT NULL DEFAULT 0,
  `company_bank_account` varchar(255) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `narration` text DEFAULT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT 0,
  `added_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- For existing tables (rename old bank columns if needed):
-- ALTER TABLE `vendor_payments` ADD COLUMN `payment_method` ENUM('cash', 'cheque','') NOT NULL DEFAULT '' AFTER `payment_type`;
-- ALTER TABLE `vendor_payments` CHANGE `bank_account` `company_bank` int(11) NOT NULL DEFAULT 0;
-- ALTER TABLE `vendor_payments` CHANGE `bank_account_name` `company_bank_account` varchar(255) DEFAULT NULL;
