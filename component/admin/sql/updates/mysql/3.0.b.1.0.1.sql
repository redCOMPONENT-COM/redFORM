INSERT INTO #__rwf_form_field (`form_id`, `field_id`, `validate`, `unique`, `published`, `readonly`, `ordering`) SELECT `form_id`, `id` AS `field_id`, `validate`, `unique`, `published`, `readonly`, `ordering` FROM #__rwf_fields;

