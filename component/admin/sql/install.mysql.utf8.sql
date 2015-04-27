CREATE TABLE IF NOT EXISTS `#__rwf_fields` (
  `id` int(11) NOT NULL auto_increment,
  `field` varchar(255) NOT NULL default '',
  `field_header` varchar(255) NOT NULL default '',
  `fieldtype` varchar(30) NOT NULL default 'textfield',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `redmember_field` varchar(20) NULL default NULL,
  `default` varchar(255) default NULL,
  `tooltip` varchar(255) default NULL,
  `params` text default NULL,
  PRIMARY KEY  (`id`)
) COMMENT='Fields for redFORM';

CREATE TABLE IF NOT EXISTS `#__rwf_forms` (
  `id` int(11) NOT NULL auto_increment,
  `formname` varchar(100) NOT NULL default 'NoName',
  `startdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `enddate` datetime NOT NULL default '0000-00-00 00:00:00',
  `published` int(1) NOT NULL default '0',
  `checked_out` int(11) default NULL,
  `checked_out_time` datetime default '0000-00-00 00:00:00',
  `submissionsubject` varchar(255) NOT NULL default '',
  `submissionbody` text NOT NULL,
  `showname` int(1) NOT NULL default '0',
  `classname` varchar(45) default NULL,
  `contactpersoninform` tinyint(1) NOT NULL default '0',
  `contactpersonemail` varchar(255) default NULL,
  `contactpersonemailsubject` varchar(255) default NULL,
  `contactpersonfullpost` int(11) NOT NULL default '0',
  `submitterinform` tinyint(1) NOT NULL default '0',
  `submitnotification` tinyint(1) NOT NULL default '0',
  `enable_confirmation` tinyint(1) NOT NULL DEFAULT '0',
  `enable_confirmation_notification` tinyint(1) NOT NULL DEFAULT '0',
  `confirmation_notification_recipients` text default NULL,
  `confirmation_contactperson_subject` varchar(255) default NULL,
  `confirmation_contactperson_body` text DEFAULT NULL,
  `redirect` VARCHAR( 300 ) NULL DEFAULT NULL,
  `notificationtext` text NOT NULL,
  `formexpires` tinyint(1) NOT NULL default '1',
  `captchaactive` tinyint(1) NOT NULL default '0',
  `access` tinyint(3) NOT NULL default '0',
  `activatepayment` tinyint(2) NOT NULL DEFAULT '0',
  `currency` varchar(3) DEFAULT NULL,
  `paymentprocessing` text DEFAULT NULL,
  `paymentaccepted` text DEFAULT NULL,
  `contactpaymentnotificationsubject` text DEFAULT NULL,
  `contactpaymentnotificationbody` text DEFAULT NULL,
  `submitterpaymentnotificationsubject` text DEFAULT NULL,
  `submitterpaymentnotificationbody` text DEFAULT NULL,
  `cond_recipients` TEXT NULL DEFAULT NULL,
  PRIMARY KEY  (`id`)
) COMMENT='Forms for redFORM';

CREATE TABLE IF NOT EXISTS `#__rwf_form_field` (
  `id` int(11) NOT NULL auto_increment,
  `form_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `validate` tinyint(1) NOT NULL DEFAULT '0',
  `published` int(11) NOT NULL default '0',
  `unique` tinyint(1) NOT NULL DEFAULT '0',
  `readonly` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `form_id` (`form_id`),
  KEY `field_id` (`field_id`)
) COMMENT='form field relation';

CREATE TABLE IF NOT EXISTS `#__rwf_submitters` (
  `id` int(11) NOT NULL auto_increment,
  `form_id` int(11) NOT NULL default '0',
  `submission_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `submission_ip` VARCHAR(50) NOT NULL,
  `confirmed_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `confirmed_ip` VARCHAR(50) NOT NULL,
  `confirmed_type` VARCHAR(50)  NULL DEFAULT 'email',
  `integration` VARCHAR(30) NULL DEFAULT NULL,
  `answer_id` int(11) NOT NULL default '0',
  `submitternewsletter` int(11) NOT NULL default '0',
  `rawformdata` text NOT NULL,
  `submit_key` varchar(45) NOT NULL,
  `price` double NULL DEFAULT NULL,
  `vat` double NULL DEFAULT NULL,
  `currency` varchar(3) DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `form_id` (`form_id`),
  KEY `answer_id` (`answer_id`)
) COMMENT='Submitters for redFORM';

CREATE TABLE IF NOT EXISTS `#__rwf_values` (
  `id` int(11) NOT NULL auto_increment,
  `value` varchar(255) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `published` int(11) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `field_id` int(11) default NULL,
  `ordering` int(11) NOT NULL default '0',
  `price` double NULL DEFAULT NULL,
  `sku` varchar(255),
  PRIMARY KEY  (`id`),
  KEY `field_id` (`field_id`)
) COMMENT='Stores fields options';

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

CREATE TABLE IF NOT EXISTS `#__rwf_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_request_id` int(11) NOT NULL,
  `date` datetime DEFAULT NULL,
  `gateway` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `data` text NOT NULL,
  `paid` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `submit_key` (`submit_key`)
) COMMENT='logging gateway notifications';
