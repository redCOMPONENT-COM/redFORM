<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 *
 */

/* No direct access */
defined('_JEXEC') or die('Restricted access');

/**
 */
class TableSubmitters extends JTable {
	/** @var int Primary key */
	var $id = null;
	/** @var int Unique key that identifies single registrations */
	var $submit_key = null;
	/** @var string The form ID */
	var $form_id = null;
	/** @var string Date and time form was submitted */
	var $submission_date = null;
	/** @var int The cross reference ID of the redEVENT event/venue/date */
	var $xref = null;
	/** @var int The ID of the submitter data */
	var $answer_id = null;
	/** @var bool If the submitter wants to be signed up to the newsletter  */
	var $submitternewsletter = null;
	/** @var string Holds the serialized post data from the forms  */
	var $rawformdata = null;
	/** @var integer */     		
	var $maxattendees 				= null;
	/** @var integer */     		
	var $maxwaitinglist 			= null;
	/** @var boolean */
	var $confirmed	= null;
	/** @var datetime */
	var $confirmdate 	= null;
	
	/**
	* @param database A database connector object
	*/
	function __construct( &$db ) {
		parent::__construct('#__rwf_submitters', 'id', $db );
	}
}
?>