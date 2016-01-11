SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS `#__rwf_section` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL default '',
  `class` varchar(100) NOT NULL default '',
  `ordering` int(11) NOT NULL default '0',
  `checked_out` int(11) NOT NULL default '0',
  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) COMMENT='form sections';

INSERT INTO `#__rwf_section` (`id`, `name`) VALUES (1, 'general');

ALTER TABLE `#__rwf_form_field`
	ADD `section_id` int(11) NOT NULL,
	ADD INDEX `section_id` (`section_id`);

UPDATE `#__rwf_form_field` SET `section_id` = 1;

SET FOREIGN_KEY_CHECKS=1;
