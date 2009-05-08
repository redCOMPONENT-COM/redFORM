<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 *
 * Acajoom user table
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