ALTER TABLE `#__rwf_forms`
	ADD `enable_confirmation` tinyint(1) NOT NULL DEFAULT '0',
  ADD `enable_confirmation_notification` tinyint(1) NOT NULL DEFAULT '0',
  ADD `confirmation_contactperson_subject` varchar(255) default NULL,
  ADD `confirmation_contactperson_body` text DEFAULT NULL;
