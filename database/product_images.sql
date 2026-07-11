CREATE TABLE `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_main` tinyint(1) NOT NULL DEFAULT 0,
  `product_id` int(11) DEFAULT NULL,
  `variation_id` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `type` enum('image','video') NOT NULL DEFAULT 'image',
  `sort` int(11) NOT NULL DEFAULT 0,
  `last_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
