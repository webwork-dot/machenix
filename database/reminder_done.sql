CREATE TABLE `reminder_done` (
  `id` int(11) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','done','') NOT NULL DEFAULT 'pending',
  `done_date` datetime DEFAULT NULL,
  `reminder_date` datetime NOT NULL,
  `added_by_name` varchar(150) NOT NULL,
  `added_by_id` int(11) NOT NULL,
  `added_by_role` int(11) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
