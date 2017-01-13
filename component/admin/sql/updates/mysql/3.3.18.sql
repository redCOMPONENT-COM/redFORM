SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `#__rwf_submitters`
	ADD `user_id` int(11) NOT NULL,
	ADD INDEX `user_id` (`user_id`);

SET FOREIGN_KEY_CHECKS=1;
