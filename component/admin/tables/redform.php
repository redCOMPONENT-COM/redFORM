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
class RedformTableRedform extends JTable {
	
	/**
	* @param database A database connector object
	*/
	function __construct( &$db ) {
		parent::__construct('#__rwf_forms', 'id', $db );
	}
	
	/**
	 * returns fields records ids associated to this form
	 *
	 * @return array
	 */
	function getFormFields()
	{
		if (empty($this->id)) {
			return array();
		}
		
		$db =& $this->getDBO();
		
		$query = ' SELECT * FROM #__rwf_fields WHERE form_id ='. $db->Quote($this->id);
		$db->setQuery($query);
		return $db->loadResultArray();
	}
	
	function store( $updateNulls=false )
	{
		$this->classname = trim($this->classname);
		return parent::store($updateNulls);
	}
}
?>