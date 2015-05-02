CREATE TABLE IF NOT EXISTS `#__rwf_invoice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cart_id` int(11) NOT NULL default '0',
  `date` datetime DEFAULT NULL,
  `reference` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `note` varchar(250) NOT NULL,
  `booked` tinyint(2) NOT NULL,
  `turned` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_id` (`cart_id`)
) COMMENT='accounting invoices references per cart id';
