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
}
?>