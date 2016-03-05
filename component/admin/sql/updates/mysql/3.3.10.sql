SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS `#__rwf_invoice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cart_id` int(11) NOT NULL default '0',
  `date` datetime DEFAULT NULL,
  `reference` varchar(100) NOT NULL default '',
  `name` varchar(100) NOT NULL default '',
  `note` varchar(250) NOT NULL default '',
  `booked` tinyint(2) NOT NULL default '0',
  `turned` int(11) NOT NULL default '0',
  `params` text NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `cart_id` (`cart_id`)
) COMMENT='accounting invoices references per cart id';

SET FOREIGN_KEY_CHECKS=1;
