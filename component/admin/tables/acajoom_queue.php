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
class TableAcajoom_queue extends JTable {
	/** @var int Primary key */
	var $qid = null;
	/** @var int The user ID */
	var $type = 1;
	/** @var string The name of the subscriber */
	var $subscriber_id = null;
	/** @var string E-mail address of subscriber */
	var $list_id = null;
	/** @var boolean Receive HTML messages */
	var $mailing_id = 0;
	/** @var boolean Subscriber confirmed or not */
	var $send_date = '0000-00-00 00:00:00';
	/** @var boolean Subscriber blacklisted or not */
	var $suspend = 0;
	/** @var string Subscriber timezone */
	var $delay = 0;
	/** @var string Language ISO */
	var $acc_level = null;
	/** @var string Subscriber signup date */
	var $published = 0;
	/** @var string Parameters */
	var $params = null;
	
	/**
	* @param database A database connector object
	*/
	function __construct( &$db ) {
		parent::__construct('#__acajoom_queue', 'qid', $db );
	}
	
	function check()
	{
		// check unicity of subscriber/list/mailing
		$query = ' SELECT qid ' 
		       . ' FROM #__acajoom_queue ' 
		       . ' WHERE subscriber_id = ' . $this->_db->Quote($this->subscriber_id)
		       . '   AND list_id = ' . $this->_db->Quote($this->list_id)
		       . '   AND mailing_id = ' . $this->_db->Quote($this->mailing_id)
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadResult();
		if ($res) {
			$this->setError(JText::_('REDFORM_ACAJOOM_EMAIL_ADDRESS_ALREADY_SUBSCRIBED'));
			return false;
		}
		
		return true;
	}
}
?>