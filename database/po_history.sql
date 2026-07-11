CREATE TABLE `po_history` (
  `id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `json` text NOT NULL,
  `added_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
