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
class RedformTableSubmitters extends JTable {
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
//	/** @var integer */     		
//	var $maxwaitinglist 			= null;
//	/** @var boolean */
//	var $confirmed	= null;
//	/** @var datetime */
//	var $confirmdate 	= null;
	/** @var string integration key */
	var $integration 	= null;
	
	/**
	* @param database A database connector object
	*/
	function __construct( &$db ) {
		parent::__construct('#__rwf_submitters', 'id', $db );
	}
}
?>