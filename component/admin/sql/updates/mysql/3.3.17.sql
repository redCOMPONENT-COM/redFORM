SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `#__rwf_cart`
	ADD `invoice_id` VARCHAR(100) NOT NULL default '';

SET FOREIGN_KEY_CHECKS=1;
