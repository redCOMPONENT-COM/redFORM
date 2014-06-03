<?php
/**
 * @package     Redform.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Field Model
 *
 * @package     Redform.Backend
 * @subpackage  Models
 * @since       2.5
 */
class RedformModelFormfield extends RModelAdmin
{
	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   11.1
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if (!$item->form_id)
		{
			$input = JFactory::getApplication()->input;
			$jform = $input->get('jform', '', 'array');

			$item->form_id = $jform['form_id'];
		}

		return $item;
	}
}
