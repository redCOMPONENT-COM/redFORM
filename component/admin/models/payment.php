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
 * Form Model
 *
 * @package     Redform.Backend
 * @subpackage  Models
 * @since       2.5
 */
class RedformModelPayment extends RModelAdmin
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

		if (!$item->id)
		{
			$submit_key = $this->getState($this->getName() . '.submit_key');
			$item->submit_key = $submit_key;
		}

		return $item;
	}


	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return  void
	 */
	protected function populateState()
	{
		parent::populateState();

		$submit_key = JFactory::getApplication()->input->getCmd('submit_key', '');
		$this->setState($this->getName() . '.submit_key', $submit_key);
	}
}
