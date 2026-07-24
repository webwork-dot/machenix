CREATE TABLE `product_variations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `product_mrp` decimal(16,5) NOT NULL DEFAULT 0.00,
  `costing_price` decimal(16,5) NOT NULL DEFAULT 0.00,
  `usd_rate` decimal(16,5) NOT NULL DEFAULT 0.00,
  `actual_usd_rate` decimal(16,2) NOT NULL DEFAULT 0.00,
  `rate` decimal(16,5) NOT NULL DEFAULT 0.00,
  `intimation` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
