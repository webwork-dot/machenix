CREATE TABLE `reserved_order_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(250) NOT NULL,
  `item_code` varchar(250) DEFAULT NULL,
  `quantity` varchar(250) NOT NULL,
  `batch_no` varchar(250) DEFAULT NULL,
  `return_qty` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
