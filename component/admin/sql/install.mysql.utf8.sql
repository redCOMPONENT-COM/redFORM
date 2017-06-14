CREATE TABLE IF NOT EXISTS `#__rwf_billinginfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `uniqueid` varchar(255) NOT NULL DEFAULT '',
  `fullname` varchar(150) NOT NULL DEFAULT '',
  `company` varchar(150) NOT NULL DEFAULT '',
  `iscompany` tinyint(1) NOT NULL DEFAULT '0',
  `vatnumber` varchar(150) NOT NULL DEFAULT '',
  `address` text NOT NULL DEFAULT '',
  `city` varchar(150) NOT NULL DEFAULT '',
  `zipcode` varchar(150) NOT NULL DEFAULT '',
  `phone` varchar(150) NOT NULL DEFAULT '',
  `email` varchar(150) NOT NULL DEFAULT '',
  `country` varchar(3) NOT NULL DEFAULT '',
  `params` text NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `cart_id` (`cart_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='billing info for cart';

CREATE TABLE IF NOT EXISTS `#__rwf_fields` (
  `id` int(11) NOT NULL auto_increment,
  `field` varchar(255) NOT NULL,
  `field_header` varchar(255) NOT NULL default '',
  `fieldtype` varchar(30) NOT NULL default 'textfield',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `redmember_field` varchar(20) NOT NULL DEFAULT '',
  `default` varchar(255) NOT NULL DEFAULT '',
  `tooltip` varchar(255) NOT NULL DEFAULT '',
  `params` text NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Fields for redFORM';

CREATE TABLE IF NOT EXISTS `#__rwf_forms` (
  `id` int(11) NOT NULL auto_increment,
  `formname` varchar(100) NOT NULL default 'NoName',
  `startdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `enddate` datetime NOT NULL default '0000-00-00 00:00:00',
  `published` int(1) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `submissionsubject` varchar(255) NOT NULL default '',
  `submissionbody` text NOT NULL default '',
  `submissionattachment` VARCHAR(255)  NULL DEFAULT NULL,
  `showname` int(1) NOT NULL default '0',
  `classname` varchar(45) NOT NULL default '',
  `contactpersoninform` tinyint(1) NOT NULL default '0',
  `contactpersonemail` varchar(255) NOT NULL default '',
  `admin_notification_email_mode` tinyint(2) NOT NULL default '0',
  `admin_notification_email_subject` varchar(255) NOT NULL default '',
  `admin_notification_email_body` text NOT NULL default '',
  `contactpersonfullpost` int(11) NOT NULL default '0',
  `submitterinform` tinyint(1) NOT NULL default '0',
  `submitnotification` tinyint(1) NOT NULL default '0',
  `enable_confirmation` tinyint(1) NOT NULL DEFAULT '0',
  `enable_confirmation_notification` tinyint(1) NOT NULL DEFAULT '0',
  `confirmation_notification_recipients` text NOT NULL default '',
  `confirmation_contactperson_subject` varchar(255) NOT NULL default '',
  `confirmation_contactperson_body` text NOT NULL default '',
  `redirect` VARCHAR( 300 ) NOT NULL default '',
  `notificationtext` text NOT NULL,
  `formexpires` tinyint(1) NOT NULL default '1',
  `captchaactive` tinyint(1) NOT NULL default '0',
  `access` tinyint(3) NOT NULL default '0',
  `activatepayment` tinyint(2) NOT NULL DEFAULT '0',
  `requirebilling` tinyint(2) NOT NULL DEFAULT '0',
  `currency` varchar(3) NOT NULL default '',
  `paymentprocessing` text NOT NULL default '',
  `paymentaccepted` text NOT NULL default '',
  `contactpaymentnotificationsubject` text NOT NULL default '',
  `contactpaymentnotificationbody` text NOT NULL default '',
  `submitterpaymentnotificationsubject` text NOT NULL default '',
  `submitterpaymentnotificationbody` text NOT NULL default '',
  `cond_recipients` text NOT NULL default '',
  `params` text NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Forms for redFORM';

CREATE TABLE IF NOT EXISTS `#__rwf_form_field` (
  `id` int(11) NOT NULL auto_increment,
  `form_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `validate` tinyint(1) NOT NULL DEFAULT '0',
  `published` int(11) NOT NULL default '0',
  `unique` tinyint(1) NOT NULL DEFAULT '0',
  `readonly` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `form_id` (`form_id`),
  KEY `field_id` (`field_id`),
  KEY `section_id` (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='form field relation';

CREATE TABLE IF NOT EXISTS `#__rwf_submitters` (
  `id` int(11) NOT NULL auto_increment,
  `form_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `submission_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `submission_ip` VARCHAR(50) NOT NULL default '',
  `confirmed_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `confirmed_ip` VARCHAR(50) NOT NULL default '',
  `confirmed_type` VARCHAR(50)  NOT NULL default 'email',
  `integration` VARCHAR(30) NOT NULL default '',
  `answer_id` int(11) NOT NULL default '0',
  `submitternewsletter` int(11) NOT NULL default '0',
  `rawformdata` text NOT NULL default '',
  `submit_key` varchar(45) NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL default '0.0',
  `vat` DECIMAL(10, 2) NOT NULL default '0.0',
  `currency` varchar(3) NOT NULL default '',
  `language` char(7) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `form_id` (`form_id`),
  KEY `answer_id` (`answer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Submitters for redFORM';

CREATE TABLE IF NOT EXISTS `#__rwf_submission_price_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submission_id` int(11) NOT NULL,
  `sku` varchar(255) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `price` DECIMAL(10, 2) NOT NULL default '0.0',
  `vat` DECIMAL(10, 2) NOT NULL default '0.0',
  PRIMARY KEY (`id`),
  KEY `submission_id` (`submission_id`),
  KEY `sku` (`sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='submissions price items';

CREATE TABLE IF NOT EXISTS `#__rwf_values` (
  `id` int(11) NOT NULL auto_increment,
  `value` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `published` int(11) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `field_id` int(11) NOT NULL,
  `ordering` int(11) NOT NULL default '0',
  `price` DECIMAL(10, 2) NOT NULL default '0.0',
  `sku` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `field_id` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores fields options';

CREATE TABLE IF NOT EXISTS `#__rwf_payment_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submission_id` int(11) NOT NULL,
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `price` DECIMAL(10, 2) NOT NULL default '0.0',
  `vat` DECIMAL(10, 2) NOT NULL default '0.0',
  `currency` varchar(3) NOT NULL default '',
  `paid` tinyint(2) NOT NULL default '0',
  `note` text NOT NULL default '',
  PRIMARY KEY (`id`),
  KEY `submission_id` (`submission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='payment requests';

CREATE TABLE IF NOT EXISTS `#__rwf_payment_request_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_request_id` int(11) NOT NULL,
  `sku` varchar(255) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `price` DECIMAL(10, 2) NOT NULL default '0.0',
  `vat` DECIMAL(10, 2) NOT NULL default '0.0',
  PRIMARY KEY (`id`),
  KEY `payment_request_id` (`payment_request_id`),
  KEY `sku` (`sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='payment request items';

CREATE TABLE IF NOT EXISTS `#__rwf_cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` VARCHAR (255) NOT NULL,
  `created` datetime NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL default '0.0',
  `vat` DECIMAL(10, 2) NOT NULL default '0.0',
  `currency` varchar(3) NOT NULL default '',
  `paid` tinyint(2) NOT NULL default '0',
  `note` text NOT NULL default '',
  `invoice_id` VARCHAR(100) NOT NULL default '',
  PRIMARY KEY (`id`),
  KEY `reference` (`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='payment cart';

CREATE TABLE IF NOT EXISTS `#__rwf_cart_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cart_id` int(11) NOT NULL,
  `payment_request_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_id` (`cart_id`),
  KEY `payment_request_id` (`payment_request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='payment cart item';

CREATE TABLE IF NOT EXISTS `#__rwf_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cart_id` int(11) NOT NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `gateway` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL default '',
  `data` text NOT NULL default '',
  `paid` tinyint(2) NOT NULL default '0',
  PRIMARY KEY (`id`),
  KEY `cart_id` (`cart_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='logging gateway notifications';

CREATE TABLE IF NOT EXISTS `#__rwf_section` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `class` varchar(100) NOT NULL default '',
  `ordering` int(11) NOT NULL default '0',
  `description` text NOT NULL default '',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='form sections';

