<?php
/**
 * @package    Redform.front
 *
 * @copyright  Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Email confirm model
 *
 * @package  Redform.front
 * @since    3.0
 */
class RedformModelConfirm extends RModel
{
	/**
	 * Confirm a submission
	 *
	 * @param   string  $key  submit key
	 *
	 * @return bool true if at least one row was updated
	 */
	public function confirm($key)
	{
		$date = JFactory::getDate()->toSql();

		$query = $this->_db->getQuery(true);
		$query->select('id')
			->from('#__rwf_submitters')
			->where('confirmed_date = 0')
			->where('submit_key = ' . $this->_db->quote($key));

		$this->_db->setQuery($query);
		$ids = $this->_db->loadColumn();

		if (!is_array($ids) || !count($ids))
		{
			return false;
		}

		$query->update('#__rwf_submitters')
			->set('confirmed_date = ' . $this->_db->quote($date))
			->where('submit_key = ' . $this->_db->quote($key));

		$this->_db->setQuery($query);
		$this->_db->execute();

		$this->setState('updatedIds', $ids);

		JPluginHelper::importPlugin('redform');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onSubmissionConfirmed', array($ids));

		return true;
	}
}
