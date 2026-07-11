CREATE TABLE `goods_return_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_order_id` varchar(255) DEFAULT NULL,
  `sop_id` int(11) NOT NULL DEFAULT 0 COMMENT 'sales_order_product ID',
  `product_name` varchar(250) NOT NULL,
  `size_id` int(11) NOT NULL,
  `size_name` varchar(255) NOT NULL,
  `group_id` varchar(255) NOT NULL,
  `color_id` int(11) NOT NULL,
  `color_name` varchar(255) NOT NULL,
  `item_code` varchar(250) DEFAULT NULL,
  `quantity` varchar(250) NOT NULL,
  `batch_no` varchar(250) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
