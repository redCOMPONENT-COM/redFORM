<?php
/**
 * @package     Redform.plugins
 * @subpackage  mailing
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die( 'Restricted access');

/**
 * Table class
 *
 * @package     Redform.plugins
 * @subpackage  mailing
 * @since       2.5
 */
class TableJNews_subscribers extends JTable
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
		parent::__construct('#__jnews_subscribers', 'id', $db);
	}

	/**
	 * returns the subscriber having this email, otherwise returns false
	 *
	 * @param   string  $mail  mail to look up
	 *
	 * @return object or false if not found
	 */
	public function find($mail)
	{
		$query = ' SELECT id FROM #__jnews_subscribers WHERE email = ' . $this->_db->Quote($mail);
		$this->_db->setQuery($query);
		$res = $this->_db->loadResult();

		if (!$res)
		{
			return false;
		}

		$this->load($res);

		return $this;
	}
}
