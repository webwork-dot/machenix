CREATE TABLE `loading_product_total` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `pkg_ctn` decimal(16,2) NOT NULL DEFAULT 0.00,
  `nw_kg` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `total_nw_kg` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `gw_kg` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `total_gw_kg` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `length` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `width` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `height` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `total_cbm_value` decimal(16,5) NOT NULL DEFAULT 0.00000,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=213 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
