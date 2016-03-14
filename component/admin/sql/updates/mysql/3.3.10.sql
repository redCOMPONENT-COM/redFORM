SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `#__rwf_forms`
	ADD `admin_notification_email_mode` tinyint(2) NOT NULL default '0',
	ADD `admin_notification_email_subject` varchar(255) NOT NULL default '',
	ADD `admin_notification_email_body` text NOT NULL default '';

SET FOREIGN_KEY_CHECKS=1;
