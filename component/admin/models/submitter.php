<?php
/**
 * @package     Redform.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Submitter Model
 *
 * @package     Redform.Backend
 * @subpackage  Models
 * @since       2.5
 */
class RedformModelSubmitter extends RModelAdmin
{
	/**
	 * Method to confirm one or more records.
	 *
	 * @param   array  $pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   3.3.19
	 */
	public function confirm(&$pks)
	{
		$pks = (array) $pks;
		$table = $this->getTable();

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk) && !RdfHelper::isNonNullDate($table->confirmed_date))
			{
				$table->confirmed_date = JFactory::getDate()->toSql();
				$table->confirmed_type = 'admin';

				$table->store();
			}
			else
			{
				$this->setError($table->getError());

				return false;
			}
		}

		JPluginHelper::importPlugin('redform');
		$dispatcher = RFactory::getDispatcher();
		$dispatcher->trigger('onSubmissionConfirmed', array($pks));

		return true;
	}
	/**
	 * Method to unconfirm one or more records.
	 *
	 * @param   array  $pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   3.3.19
	 */
	public function unconfirm(&$pks)
	{
		$pks = (array) $pks;
		$table = $this->getTable();

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				$table->confirmed_date = '';
				$table->confirmed_type = '';

				$table->store();
			}
			else
			{
				$this->setError($table->getError());

				return false;
			}
		}

		JPluginHelper::importPlugin('redform');
		$dispatcher = RFactory::getDispatcher();
		$dispatcher->trigger('onSubmissionUnconfirmed', array($pks));

		return true;
	}
}
