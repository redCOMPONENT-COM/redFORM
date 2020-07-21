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
 * Acymailing list Field
 *
 * @since  3.3.19
 */
class RedformFormFieldAcymailinglist extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'acymailinglist';

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
		if (include_once(JPATH_ADMINISTRATOR . '/components/com_acym/helpers/helper.php'))
		{
			$listClass = acym_get('class.list');

			$options = array_map(
				function ($list)
				{
					return JHtml::_('select.option', $list->id, $list->name);
				},
				$listClass->getAll()
			);
		}
		elseif (include_once(JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php'))
		{
			$listClass = acymailing_get('class.list');

			$options = array_map(
				function ($list)
				{
					return JHtml::_('select.option', $list->listid, $list->name);
				},
				$listClass->getLists()
			);
		}
		else
		{
			JFactory::getApplication()->enqueueMessage('Acymailing not installed, or incompatible version');

			return parent::getOptions();
		}

		return array_merge(parent::getOptions(), $options);
	}
}
