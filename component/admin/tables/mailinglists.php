<?php
/**
 * @package  Redform
 * @copyright  Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license    GNU/GPL, see LICENSE.php
 * redFORM can be downloaded from www.redcomponent.com
 * redFORM is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redFORM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redFORM; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/* No direct access */
defined('_JEXEC') or die('Restricted access');

/**
 * mailing list table
 *
 * @package  Redform
 * @since    1.0
*/
class RedformTableMailinglists extends JTable
{
	/**
	 * constructor
	 *
	 * @param   object  $db  database object
	 */
	public function __construct($db)
	{
		parent::__construct('#__rwf_mailinglists', 'field_id', $db );
	}

	/**
	 * (non-PHPdoc)
	 * @see JTable::store()
	 */
	public function store($updateNulls = false)
	{
		$k = $this->check();

		if ($k)
		{
			$ret = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key);
		}
		else
		{
			$ret = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
		}

		if (!$ret)
		{
			$this->setError(get_class($this) . '::store failed - ' . $this->_db->getErrorMsg());
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Check if there is an entry for this email field
	 *
	 * @return boolean
	 */
	public function check()
	{
		$db = JFactory::getDBO();

		$q = "SELECT COUNT(*) AS total FROM ".$this->_tbl." WHERE field_id = ".$this->field_id;
		$db->setQuery($q);
		$result = $db->loadResult();

		if ($result > 0)
		{
			return true;
		}
		else
		{
			return false;
		}

	}
}