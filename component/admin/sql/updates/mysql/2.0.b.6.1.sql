ALTER TABLE `#__rwf_submitters` ADD `currency` varchar(3) DEFAULT NULL;

UPDATE `#__rwf_submitters` AS s
INNER JOIN `#__rwf_forms` AS f on s.form_id = f.id
SET s.currency = f.currency
