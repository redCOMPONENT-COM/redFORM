ALTER TABLE `#__rwf_submitters` ADD `vat` double NULL DEFAULT NULL;
ALTER TABLE `#__rwf_values` ADD `sku` varchar(255);

CREATE TABLE IF NOT EXISTS `#__rwf_payment_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submit_key` varchar(255) NOT NULL,
  `created` datetime DEFAULT NULL,
  `price` double NULL DEFAULT NULL,
  `vat` double NULL DEFAULT NULL,
  `currency` varchar(3) DEFAULT NULL,
  `paid` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `submit_key` (`submit_key`)
) COMMENT='submissions payment requests';

ALTER TABLE `#__rwf_payment` ADD payment_request_id` int(11) NOT NULL,
	ADD INDEX ( `payment_request_id` ) ;

INSERT INTO `#__rwf_payment_request` (`submit_key`, `created`, `price`, `vat`, `currency`)
	SELECT `s`.`submit_key`, `s`.`submission_date`, `s`.`price`, 0, `s`.`currency`
	FROM `#__rwf_submitters` AS s
	WHERE `s`.`price` > 0
	GROUP BY `s`.`submit_key`;

UPDATE `#__rwf_payment` AS `p`
	INNER JOIN `#__rwf_payment_request` AS `pr` ON `pr`.`submit_key` = `p`.`submit_key`
	SET `p`.`payment_request_id` = `pr`.`id`;

UPDATE `#__rwf_payment_request` AS `pr`
	INNER JOIN `#__rwf_payment` AS `p` ON `pr`.`id` = `p`.`payment_request_id` AND `p`.`paid` = 1
	SET `pr`.`paid` = 1;
