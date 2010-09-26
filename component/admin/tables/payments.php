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
class RedformTablePayments extends JTable {
	/** @var int Primary key */
	var $id = null;
	/** @var int Unique key that identifies single registrations */
	var $submit_key = null;
	/** @var string date */
	var $date = null;
	/** @var string gateway */
	var $gateway = null;
	/** @var string status */
	var $status = null;
	/** @var string information */
	var $data = null;
	/** @var int paid */
	var $paid = 0;
	
	/**
	* @param database A database connector object
	*/
	function __construct( &$db ) {
		parent::__construct('#__rwf_payment', 'id', $db );
	}
	
	function check()
	{
		if (empty($this->submit_key)) {
			$this->setError(Jtext::_('COM_REDFORM_PAYMENT_TABLE_SUBMIT_KEY_IS_REQUIRED'));
			return false;
		}
		return true;
	}
}
?>