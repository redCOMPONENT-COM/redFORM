SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `#__rwf_forms`
	CHANGE `formexpires` `formexpires` tinyint(1) NOT NULL default '0';

SET FOREIGN_KEY_CHECKS=1;
