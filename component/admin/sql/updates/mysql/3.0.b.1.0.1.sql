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

INSERT INTO #__rwf_form_field (`form_id`, `field_id`, `validate`, `unique`, `published`, `readonly`, `ordering`) SELECT `form_id`, `id` AS `field_id`, `validate`, `unique`, `published`, `readonly`, `ordering` FROM #__rwf_fields;

