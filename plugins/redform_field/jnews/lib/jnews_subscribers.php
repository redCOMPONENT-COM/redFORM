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
class TableJnews_subscribers extends RTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'jnews_subscribers';

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
