<?php
/**
 * @package     Redform.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * redFORM Section Field
 *
 * @package     Redform.Library
 * @subpackage  Fields
 * @since       3.3.8
 */
class RedformFormFieldSection extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'section';

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
		$options = array();

		// Get the sections
		$model = RModel::getAdminInstance('Sections', array('ignore_request' => true), 'com_redform');
		$model->setState('list.limit', 0);
		$items = $model->getItems();

		// Build the field options
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$options[] = JHtml::_('select.option', $item->id, $item->name);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
