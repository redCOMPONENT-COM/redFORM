<?php
/**
 * @package     Redform.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

JFormHelper::loadFieldClass('list');

/**
 * Redform mailing list type select field
 *
 * @package     Redform.Library
 * @subpackage  Fields
 * @since       2.5
 */
class JFormFieldRedformmailinglisttype extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'Redformmailinglisttype';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$res = array();
		JPluginHelper::importPlugin('redform_mailing');
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger('getIntegrationName', array(&$res));

		$options = array();

		if (count($res))
		{
			foreach ($res as $name)
			{
				$options[] = JHtml::_('select.option', $name, $name);
			}
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
