SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `#__rwf_submitters`
	ADD `language` char(7) NOT NULL;

SET FOREIGN_KEY_CHECKS=1;
