CREATE TABLE IF NOT EXISTS `#__rwf_configuration` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) COMMENT='Configuration for redFORM';

CREATE TABLE IF NOT EXISTS `#__rwf_fields` (
  `id` int(11) NOT NULL auto_increment,
  `field` varchar(255) NOT NULL default '0',
  `published` int(11) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `form_id` int(11) default NULL,
  `ordering` int(11) NOT NULL default '0',
  `redmember_field` varchar(20) NULL default NULL,
  `validate` tinyint(1) NOT NULL DEFAULT '0',
  `unique` tinyint(1) NOT NULL DEFAULT '0',
  `tooltip` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) COMMENT='Fields for redFORM';

CREATE TABLE IF NOT EXISTS `#__rwf_forms` (
  `id` int(11) NOT NULL auto_increment,
  `formname` varchar(100) NOT NULL default 'NoName',
  `startdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `enddate` datetime NOT NULL default '0000-00-00 00:00:00',
  `published` int(1) NOT NULL default '0',
  `formstarted` int(1) NOT NULL default '0',
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
  `notificationtext` text NOT NULL,
  `formexpires` tinyint(1) NOT NULL default '1',
  `virtuemartactive` tinyint(1) NOT NULL default '0',
  `vmproductid` int(11) default NULL,
  `vmitemid` int(4) NOT NULL default '1',
  `captchaactive` tinyint(1) NOT NULL default '0',
  `access` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) COMMENT='Forms for redFORM';

CREATE TABLE IF NOT EXISTS `#__rwf_submitters` (
  `id` int(11) NOT NULL auto_increment,
  `form_id` int(11) NOT NULL default '0',
  `submission_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `xref` int(11) NOT NULL default '0',
  `answer_id` int(11) NOT NULL default '0',
  `submitternewsletter` int(11) NOT NULL default '0',
  `rawformdata` text NOT NULL,
  `submit_key` varchar(45) NOT NULL,
  `waitinglist` tinyint(1) NOT NULL default '0',
  `confirmed` tinyint(1) NOT NULL default '0',
  `confirmdate` datetime default NULL,
  PRIMARY KEY  (`id`)
) COMMENT='Submitters for redFORM';

CREATE TABLE IF NOT EXISTS `#__rwf_values` (
  `id` int(11) NOT NULL auto_increment,
  `value` varchar(255) NOT NULL default '',
  `published` int(11) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `field_id` int(11) default NULL,
  `fieldtype` varchar(25) NOT NULL default 'radio',
  `ordering` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) COMMENT='Answers for redFORM';

CREATE TABLE IF NOT EXISTS `#__rwf_mailinglists` (
  `id` int(11) unsigned NOT NULL default '0',
  `mailinglist` varchar(100) NOT NULL,
  `listnames` text NOT NULL,
  PRIMARY KEY  (`id`)
) COMMENT='Mailinglists for redFORM';


INSERT IGNORE INTO `#__rwf_configuration` (`id`, `name`, `value`) VALUES
(1, 'phplist_path', 'lists')
