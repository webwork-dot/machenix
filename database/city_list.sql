CREATE TABLE `city_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `district` varchar(250) DEFAULT NULL,
  `state_id` int(11) DEFAULT NULL,
  `added_by_id` int(11) DEFAULT NULL,
  `added_type` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2053 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
