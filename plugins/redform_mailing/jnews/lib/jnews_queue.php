<?php
/**
 * @package     Redform.plugins
 * @subpackage  mailing
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die( 'Restricted access');;

/**
 * Table class
 *
 * @package     Redform.plugins
 * @subpackage  mailing
 * @since       2.5
 */
class TableJNews_queue extends JTable
{
	/**
	 * Object constructor
	 *
	 * @param   string     $table  Name of the table to model.
	 * @param   string     $key    Name of the primary key field in the table.
	 * @param   JDatabase  &$db    JDatabase connector object.
	 */
	public function __construct($table, $key, &$db)
	{
		parent::__construct('#__jnews_queue', 'qid', $db);
	}

	/**
	 * Method to perform sanity checks
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 */
	public function check()
	{
		// Check unicity of subscriber/list/mailing
		$query = ' SELECT qid '
			. ' FROM #__jnews_queue '
			. ' WHERE subscriber_id = ' . $this->_db->Quote($this->subscriber_id)
			. '   AND type = ' . $this->_db->Quote($this->type)
			. '   AND mailing_id = ' . $this->_db->Quote($this->mailing_id);
		$this->_db->setQuery($query);
		$res = $this->_db->loadResult();

		if ($res)
		{
			$this->setError(JText::_('REDFORM_JNEWS_EMAIL_ADDRESS_ALREADY_SUBSCRIBED'));

			return false;
		}

		return true;
	}
}
