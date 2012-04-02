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
class TableJNews_queue extends JTable {
	
	/**
	* @param database A database connector object
	*/
	function __construct( &$db ) {
		parent::__construct('#__jnews_queue', 'qid', $db );
	}
	
	function check()
	{
		// check unicity of subscriber/list/mailing
		$query = ' SELECT qid ' 
		       . ' FROM #__jnews_queue ' 
		       . ' WHERE subscriber_id = ' . $this->_db->Quote($this->subscriber_id)
		       . '   AND type = ' . $this->_db->Quote($this->type)
		       . '   AND mailing_id = ' . $this->_db->Quote($this->mailing_id)
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadResult();
		if ($res) {
			$this->setError(JText::_('REDFORM_JNEWS_EMAIL_ADDRESS_ALREADY_SUBSCRIBED'));
			return false;
		}
		
		return true;
	}
}
?>