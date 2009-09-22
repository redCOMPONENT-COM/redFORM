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
class TableAcajoom_subscribers extends JTable {
	/** @var int Primary key */
	var $id = null;
	/** @var int The user ID */
	var $user_id = null;
	/** @var string The name of the subscriber */
	var $name = null;
	/** @var string E-mail address of subscriber */
	var $email = null;
	/** @var boolean Receive HTML messages */
	var $receive_html = 1;
	/** @var boolean Subscriber confirmed or not */
	var $confirmed = 1;
	/** @var boolean Subscriber blacklisted or not */
	var $blacklist = 0;
	/** @var string Subscriber timezone */
	var $timezone = '00:00:00';
	/** @var string Language ISO */
	var $language_iso = 'eng';
	/** @var string Subscriber signup date */
	var $subscribe_date = null;
	/** @var string Parameters */
	var $params = '';
	
	/**
	* @param database A database connector object
	*/
	function __construct( &$db ) {
		parent::__construct('#__acajoom_subscribers', 'id', $db );
	}
}
?>