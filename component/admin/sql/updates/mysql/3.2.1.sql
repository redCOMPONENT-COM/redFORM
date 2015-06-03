-- Clean up all null and default value of fields
ALTER TABLE `j3x_rwf_billinginfo`
	CHANGE `title` `title` varchar(255) NOT NULL DEFAULT '',
	CHANGE `uniqueid` `uniqueid` varchar(255) NOT NULL DEFAULT '',
	CHANGE `fullname` `fullname` varchar(150) NOT NULL DEFAULT '',
	CHANGE `company` `company` varchar(150) NOT NULL DEFAULT '',
	CHANGE `vatnumber` `vatnumber` varchar(150) NOT NULL DEFAULT '',
	CHANGE `address` `address` text NOT NULL DEFAULT '',
	CHANGE `city` `city` varchar(150) NOT NULL DEFAULT '',
	CHANGE `zipcode` `zipcode` varchar(150) NOT NULL DEFAULT '',
	CHANGE `phone` `phone` varchar(150) NOT NULL DEFAULT '',
	CHANGE `email` `email` varchar(150) NOT NULL DEFAULT '',
	CHANGE `country` `country` varchar(3) NOT NULL DEFAULT '';

ALTER TABLE `j3x_rwf_fields`
	CHANGE `field` `field` varchar(255) NOT NULL,
	CHANGE `redmember_field` `redmember_field` varchar(20) NOT NULL DEFAULT '',
	CHANGE `default` `default` varchar(255) NOT NULL DEFAULT '',
	CHANGE `tooltip` `tooltip` varchar(255) NOT NULL DEFAULT '',
	CHANGE `params` `params` text NOT NULL DEFAULT '';

ALTER TABLE `j3x_rwf_forms`
	CHANGE `checked_out` `checked_out` int(11) NOT NULL default '0',
	CHANGE `checked_out_time` `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
	CHANGE `submissionbody` `submissionbody` text NOT NULL default '',
	CHANGE `classname` `classname` varchar(45) NOT NULL default '',
	CHANGE `contactpersonemail` `contactpersonemail` varchar(255) NOT NULL default '',
	CHANGE `contactpersonemailsubject` `contactpersonemailsubject` varchar(255) NOT NULL default '',
	CHANGE `confirmation_notification_recipients` `confirmation_notification_recipients` text NOT NULL default '',
	CHANGE `confirmation_contactperson_subject` `confirmation_contactperson_subject` varchar(255) NOT NULL default '',
	CHANGE `confirmation_contactperson_body` `confirmation_contactperson_body` text NOT NULL default '',
	CHANGE `redirect` `redirect` VARCHAR( 300 ) NOT NULL default '',
	CHANGE `currency` `currency` varchar(3) NOT NULL default '',
	CHANGE `paymentprocessing` `paymentprocessing` text NOT NULL default '',
	CHANGE `paymentaccepted` `paymentaccepted` text NOT NULL default '',
	CHANGE `contactpaymentnotificationsubject` `contactpaymentnotificationsubject` text NOT NULL default '',
	CHANGE `contactpaymentnotificationbody` `contactpaymentnotificationbody` text NOT NULL default '',
	CHANGE `submitterpaymentnotificationsubject` `submitterpaymentnotificationsubject` text NOT NULL default '',
	CHANGE `submitterpaymentnotificationbody` `submitterpaymentnotificationbody` text NOT NULL default '',
	CHANGE `cond_recipients` `cond_recipients` text NOT NULL default '';

ALTER TABLE `j3x_rwf_form_field`
	CHANGE `ordering` `ordering` int(11) NOT NULL default '0';

ALTER TABLE `j3x_rwf_submitters`
	CHANGE `form_id` `form_id` int(11) NOT NULL,
	CHANGE `submission_ip` `submission_ip` VARCHAR(50) NOT NULL default '',
	CHANGE `confirmed_ip` `confirmed_ip` VARCHAR(50) NOT NULL default '',
	CHANGE `confirmed_type` `confirmed_type` VARCHAR(50)  NOT NULL default 'email',
	CHANGE `integration` `integration` VARCHAR(30) NOT NULL default '',
	CHANGE `rawformdata` `rawformdata` text NOT NULL default '',
	CHANGE `price` `price` DECIMAL(10, 2) NOT NULL default '0.0',
	CHANGE `vat` `vat` DECIMAL(10, 2) NOT NULL default '0.0',
	CHANGE `currency` `currency` varchar(3) NOT NULL default '';

ALTER TABLE `j3x_rwf_submission_price_item`
	CHANGE `sku` `sku` varchar(255) NOT NULL default '',
	CHANGE `label` `label` varchar(255) NOT NULL default '',
	CHANGE `price` `price` DECIMAL(10, 2) NOT NULL default '0.0',
	CHANGE `vat` `vat` DECIMAL(10, 2) NOT NULL default '0.0';

ALTER TABLE `j3x_rwf_values`
	CHANGE `value` `value` varchar(255) NOT NULL,
	CHANGE `label` `label` varchar(255) NOT NULL,
	CHANGE `field_id` `field_id` int(11) NOT NULL,
	CHANGE `price` `price` DECIMAL(10, 2) NOT NULL default '0.0',
	CHANGE `sku` `sku` varchar(255) NOT NULL default '';

ALTER TABLE `j3x_rwf_payment_request`
	CHANGE `created` `created` datetime NOT NULL default '0000-00-00 00:00:00',
	CHANGE `price` `price` DECIMAL(10, 2) NOT NULL default '0.0',
	CHANGE `vat` `vat` DECIMAL(10, 2) NOT NULL default '0.0',
	CHANGE `currency` `currency` varchar(3) NOT NULL default '',
	CHANGE `paid` `paid` tinyint(2) NOT NULL default '0',
	CHANGE `note` `note` text NOT NULL default '';

ALTER TABLE `j3x_rwf_payment_request_item`
	CHANGE `sku` `sku` varchar(255) NOT NULL default '',
	CHANGE `label` `label` varchar(255) NOT NULL default '',
	CHANGE `price` `price` DECIMAL(10, 2) NOT NULL default '0.0',
	CHANGE `vat` `vat` DECIMAL(10, 2) NOT NULL default '0.0';

ALTER TABLE `j3x_rwf_cart`
	CHANGE `created` `created` datetime NOT NULL,
	CHANGE `price` `price` DECIMAL(10, 2) NOT NULL default '0.0',
	CHANGE `vat` `vat` DECIMAL(10, 2) NOT NULL default '0.0',
	CHANGE `currency` `currency` varchar(3) NOT NULL default '',
	CHANGE `paid` `paid` tinyint(2) NOT NULL default '0',
	CHANGE `note` `note` text NOT NULL default '';

ALTER TABLE `j3x_rwf_payment`
	CHANGE `date` `date` datetime NOT NULL default '0000-00-00 00:00:00',
	CHANGE `status` `status` varchar(100) NOT NULL default '',
	CHANGE `data` `data` text NOT NULL default '',
	CHANGE `paid` `paid` tinyint(2) NOT NULL default '0';
