ALTER TABLE `#__rwf_submitters` CHANGE `price` `price` DECIMAL(10, 2) NULL DEFAULT NULL,
	ADD `vat` DECIMAL(10, 2) NULL DEFAULT NULL;

ALTER TABLE `#__rwf_values`  CHANGE `price` `price` DECIMAL(10, 2) NULL DEFAULT NULL,
	ADD `sku` varchar(255);

ALTER TABLE `#__rwf_payment` ADD `cart_id` int(11) NOT NULL,
	ADD INDEX `cart_id` (`cart_id`);

CREATE TABLE IF NOT EXISTS `#__rwf_submission_price_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submission_id` int(11) NOT NULL,
  `sku` varchar(255),
  `label` varchar(255),
  `price` DECIMAL(10, 2) NULL DEFAULT NULL,
  `vat` DECIMAL(10, 2) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `submission_id` (`submission_id`),
  KEY `sku` (`sku`)
) COMMENT='submissions price items';

CREATE TABLE IF NOT EXISTS `#__rwf_payment_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submission_id` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `price` DECIMAL(10, 2) NULL DEFAULT NULL,
  `vat` DECIMAL(10, 2) NULL DEFAULT NULL,
  `currency` varchar(3) DEFAULT NULL,
  `paid` tinyint(2) NOT NULL,
  `note` text NULL,
  PRIMARY KEY (`id`),
  KEY `submission_id` (`submission_id`)
) COMMENT='payment requests';

CREATE TABLE IF NOT EXISTS `#__rwf_payment_request_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_request_id` int(11) NOT NULL,
  `sku` varchar(255),
  `label` varchar(255),
  `price` DECIMAL(10, 2) NULL DEFAULT NULL,
  `vat` DECIMAL(10, 2) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_request_id` (`payment_request_id`),
  KEY `sku` (`sku`)
) COMMENT='payment request items';

CREATE TABLE IF NOT EXISTS `#__rwf_cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` VARCHAR (255) NOT NULL,
  `created` datetime DEFAULT NULL,
  `price` DECIMAL(10, 2) NULL DEFAULT NULL,
  `vat` DECIMAL(10, 2) NULL DEFAULT NULL,
  `currency` varchar(3) DEFAULT NULL,
  `paid` tinyint(2) NOT NULL,
  `note` text NULL,
  PRIMARY KEY (`id`),
  KEY `reference` (`reference`)
) COMMENT='payment cart';

CREATE TABLE IF NOT EXISTS `#__rwf_cart_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cart_id` int(11) NOT NULL,
  `payment_request_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_id` (`cart_id`),
  KEY `payment_request_id` (`payment_request_id`)
) COMMENT='payment cart item';

CREATE TABLE IF NOT EXISTS `#__rwf_payment_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submission_id` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `price` double NULL DEFAULT NULL,
  `vat` double NULL DEFAULT NULL,
  `currency` varchar(3) DEFAULT NULL,
  `paid` tinyint(2) NOT NULL,
  `note` text NULL,
  PRIMARY KEY (`id`),
  KEY `submission_id` (`submission_id`)
) COMMENT='submissions payment requests';

INSERT INTO `#__rwf_payment_request` (`submission_id`, `created`, `price`, `vat`, `currency`)
	SELECT `s`.`id`, `s`.`submission_date`, `s`.`price`, 0, `s`.`currency`
	FROM `#__rwf_submitters` AS s
	WHERE `s`.`price` > 0;

INSERT INTO `#__rwf_cart` (`reference`, `created`, `price`, `currency`)
	SELECT `s`.`submit_key`, `s`.`submission_date`, `s`.`price`, `s`.`currency`
	FROM `#__rwf_submitters` AS s
	WHERE `s`.`price` > 0;

INSERT INTO `#__rwf_cart_item` (`cart_id`, `payment_request_id`)
	SELECT `c`.`id`, `pr`.`id`
	FROM `#__rwf_cart` AS c
	INNER JOIN `#__rwf_submitters` AS s ON `s`.`submit_key` = `c`.`reference`
	INNER JOIN `#__rwf_payment_request` AS pr ON `pr`.`submission_id` = `s`.`id`;

UPDATE `#__rwf_payment` AS `p`
	INNER JOIN `#__rwf_cart` AS `c` ON `c`.`reference` = `p`.`submit_key`
	SET `p`.`cart_id` = `c`.`id`;

UPDATE `#__rwf_payment_request` AS `pr`
	INNER JOIN `#__rwf_cart_item` AS `ci` ON `ci`.`payment_request_id` = `pr`.`id`
	INNER JOIN `#__rwf_cart` AS `c` ON `c`.`id` = `ci`.`cart_id`
	INNER JOIN `#__rwf_payment` AS `p` ON `p`.`cart_id` = `c`.`id`
	SET `pr`.`paid` = 1
	WHERE `p`.`paid` = 1;
