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
 * Jnews list Field
 *
 * @since  3.3.19
 */
class RedformFormFieldJnewslist extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'jnewslist';

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
		if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_jnews'))
		{
			JFactory::getApplication()->enqueueMessage('Jnews not installed, or incompatible version');

			return parent::getOptions();
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('id, list_name AS name')
			->from('#__jnews_lists')
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
