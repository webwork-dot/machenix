CREATE TABLE `stock_transfer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_id` int(11) NOT NULL,
  `from_name` varchar(250) NOT NULL,
  `to_id` int(11) NOT NULL,
  `to_name` varchar(250) NOT NULL,
  `added_date` datetime NOT NULL,
  `added_by_id` int(11) NOT NULL,
  `added_by_name` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
