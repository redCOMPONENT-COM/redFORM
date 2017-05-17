<?php
/**
 * @package     Redform.plugins
 *
 * @copyright   Copyright (C) 2008 - 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Ccnewsletter list Field
 *
 * @since  __deploy_version__
 */
class RedformFormFieldCcnewsletterlist extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'ccnewsletterlist';

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
		if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_ccnewsletter'))
		{
			JFactory::getApplication()->enqueueMessage('Ccnewsletter not installed, or incompatible version');

			return parent::getOptions();
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('id, group_name as name')
			->from('#__ccnewsletter_groups')
			->order('name ASC');

		$db->setQuery($query);

		if (!$res = $db->loadObjectList())
		{
			return parent::getOptions();
		}

		$options = array_map(
			function ($row)
			{
				return JHtml::_('select.option', $row->id, $row->name);
			},
			$res
		);

		return array_merge(parent::getOptions(), $options);
	}
}
