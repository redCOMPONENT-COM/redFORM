<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license GNU/GPL, see LICENSE.php
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
 */
class TableJNews_subscribers extends JTable {
		
	/**
	* @param database A database connector object
	*/
	function __construct( &$db ) {
		parent::__construct('#__jnews_subscribers', 'id', $db );
	}
	
	/**
	 * returns the subscriber having this email, otherwise returns false
	 * 
	 * @param string $mail
	 * @return object or false if not found
	 */
	function find($mail)
	{
		$query = ' SELECT id FROM #__jnews_subscribers WHERE email = '.$this->_db->Quote($mail);
		$this->_db->setQuery($query);
		$res = $this->_db->loadResult();
		
		if ($res)
		{
			$this->load($res);
			return $this;
		}
		else
		{
			return false;
		}
	}
}
?>