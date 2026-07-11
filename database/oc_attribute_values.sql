CREATE TABLE `oc_attribute_values` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `is_homepage` tinyint(1) NOT NULL DEFAULT 0,
  `attribute_id` int(10) unsigned NOT NULL,
  `attr_name` varchar(50) DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `image` varchar(200) DEFAULT NULL,
  `color_type` varchar(10) DEFAULT NULL,
  `color_code` varchar(50) DEFAULT NULL,
  `color_image` varchar(200) DEFAULT NULL,
  `slug` varchar(120) DEFAULT NULL,
  `sort` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
