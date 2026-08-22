CREATE TABLE `sys_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `ref_id` int(11) DEFAULT NULL,
  `module` enum('','product','purchase_order','priority_list','loading_list','purchase_in','sales') NOT NULL,
  `action` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `table_name` varchar(255) DEFAULT NULL,
  `added_by` int(11) DEFAULT NULL,
  `added_by_email` varchar(355) DEFAULT NULL,
  `added_by_name` varchar(255) DEFAULT NULL,
  `added_by_type` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
