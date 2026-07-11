CREATE TABLE `state_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` int(11) NOT NULL,
  `state` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `added_by_id` int(11) DEFAULT NULL,
  `added_type` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
