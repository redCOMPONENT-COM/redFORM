<?php
/**
 * @package     Redform.plugins
 * @subpackage  mailing
 *
 * @copyright   Copyright (C) 2008 - 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Daterange list Field
 *
 * @since  3.3.19
 */
class RedformFormFieldDaterangelist extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'daterangelist';

	/**
	 * A static cache.
	 *
	 * @var  array
	 */
	protected $cache = array();

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_daterange/helpers/helper.php'))
		{
			JFactory::getApplication()->enqueueMessage('Daterange not installed, or incompatible version');

			return parent::getOptions();
		}

		require_once JPATH_ADMINISTRATOR . '/components/com_daterange/helpers/helper.php';

		$listClass = daterange_get('class.list');

		$options = array_map(
			function ($list)
			{
				return JHtml::_('select.option', $list->listid, $list->name);
			},
			$listClass->getLists()
		);

		return array_merge(parent::getOptions(), $options);
	}
}
