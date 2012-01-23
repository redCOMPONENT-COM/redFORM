CREATE TABLE IF NOT EXISTS `#__rwf_configuration` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) COMMENT='Configuration for redFORM';

CREATE TABLE IF NOT EXISTS `#__rwf_fields` (
  `id` int(11) NOT NULL auto_increment,
  `field` varchar(255) NOT NULL default '',
  `field_header` varchar(255) NOT NULL default '',
  `fieldtype` varchar(30) NOT NULL default 'textfield',
  `published` int(11) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `form_id` int(11) default NULL,
  `ordering` int(11) NOT NULL default '0',
  `redmember_field` varchar(20) NULL default NULL,
  `validate` tinyint(1) NOT NULL DEFAULT '0',
  `unique` tinyint(1) NOT NULL DEFAULT '0',
  `readonly` tinyint(1) NOT NULL DEFAULT '0',
  `default` varchar(255) default NULL,
  `tooltip` varchar(255) default NULL,
  `params` text default NULL,
  PRIMARY KEY  (`id`),
  KEY `form_id` (`form_id`)
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
  `contactpersonfullpost` int(11) NOT NULL default '0',
  `submitterinform` tinyint(1) NOT NULL default '0',
  `submitnotification` tinyint(1) NOT NULL default '0',
  `redirect` VARCHAR( 300 ) NULL DEFAULT NULL,
  `notificationtext` text NOT NULL,
  `formexpires` tinyint(1) NOT NULL default '1',
  `virtuemartactive` tinyint(1) NOT NULL default '0',
  `vmproductid` int(11) default NULL,
  `vmitemid` int(4) NOT NULL default '1',
  `captchaactive` tinyint(1) NOT NULL default '0',
  `access` tinyint(3) NOT NULL default '0',
  `activatepayment` tinyint(2) NOT NULL DEFAULT '0',
  `show_js_price` tinyint(2) NOT NULL DEFAULT '1',
  `currency` varchar(3) DEFAULT NULL,
  `paymentprocessing` text DEFAULT NULL,
  `paymentaccepted` text DEFAULT NULL,
  `contactpaymentnotificationsubject` text DEFAULT NULL,
  `contactpaymentnotificationbody` text DEFAULT NULL,
  `submitterpaymentnotificationsubject` text DEFAULT NULL,
  `submitterpaymentnotificationbody` text DEFAULT NULL,
  `cond_recipients` TEXT NULL DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `vmproductid` (`vmproductid`)
) COMMENT='Forms for redFORM';

CREATE TABLE IF NOT EXISTS `#__rwf_submitters` (
  `id` int(11) NOT NULL auto_increment,
  `form_id` int(11) NOT NULL default '0',
  `submission_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `integration` VARCHAR(30) NULL DEFAULT NULL,
  `xref` int(11) NOT NULL default '0',
  `answer_id` int(11) NOT NULL default '0',
  `submitternewsletter` int(11) NOT NULL default '0',
  `rawformdata` text NOT NULL,
  `submit_key` varchar(45) NOT NULL,
  `uniqueid` varchar(50) default NULL,
  `waitinglist` tinyint(1) NOT NULL default '0',
  `confirmed` tinyint(1) NOT NULL default '0',
  `confirmdate` datetime default NULL,
  `price` double NULL DEFAULT NULL,
  PRIMARY KEY  (`id`),
  KEY `form_id` (`form_id`),
  KEY `event_id` (`xref`),
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
  PRIMARY KEY  (`id`),
  KEY `field_id` (`field_id`)
) COMMENT='Answers for redFORM';

CREATE TABLE IF NOT EXISTS `#__rwf_mailinglists` (
  `field_id` int(11) unsigned NOT NULL default '0',
  `mailinglist` varchar(100) NOT NULL,
  `listnames` text NOT NULL,
  PRIMARY KEY  (`field_id`)
) COMMENT='Mailinglists for redFORM';

CREATE TABLE IF NOT EXISTS `#__rwf_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submit_key` varchar(255) NOT NULL,
  `date` datetime DEFAULT NULL,
  `gateway` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `data` text NOT NULL,
  `paid` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `submit_key` (`submit_key`)
) COMMENT='logging gateway notifications';

INSERT IGNORE INTO `#__rwf_configuration` (`id`, `name`, `value`) VALUES
(1, 'phplist_path', 'lists')
