CREATE TABLE `stock_transfer_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `batch_no` varchar(250) DEFAULT NULL,
  `product_name` varchar(250) NOT NULL,
  `item_code` varchar(250) DEFAULT NULL,
  `quantity` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
