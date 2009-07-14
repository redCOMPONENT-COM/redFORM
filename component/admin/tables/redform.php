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
class TableRedform extends JTable {
	/** @var int Primary key */
	var $id = null;
	/** @var string The IP address or range to block */
	var $formname = null;
	/** @var string Comment for the blocked IP */
	var $startdate = null;
	/** @var string Whether or not the entry is published */
	var $enddate = null;
	/** @var string Whether or not the entry is published */
	var $published = 0;
	/** @var string Whether or not the entry is published */
	var $formstarted = null;
	/** @var string Subject for the mail sent to new contestants */
	var $submissionsubject = null;
	/** @var string Body text for the mail sent to new contestants */
	var $submissionbody = null;
	/** @var string Whether or not the competition name is shown */
	var $showname = null;
	/** @var string CSS classname to allow individual styling */
	var $classname = null;
	/** @var string Inform contactperson on submission */
	var $contactpersoninform = 0;
	/** @var string E-mail address of the contactperson */
	var $contactpersonemail = null;
	/** @var integer E-mail the full submission to the contactperson */
	var $contactpersonfullpost = 0;
	/** @var integer E-mail confirmation to submitter */
	var $submitterinform = 0;
	/** @var integer Show notification */
	var $submitnotification = 0;
	/** @var integer Text to show on submission */
	var $notificationtext = null;
	/** @var boolean Set if a form expires or not */
	var $formexpires = 1;
	/** @var boolean Set if VirtueMart integration is enabled */
	var $virtuemartactive = 0;
	/** @var integer Set the product ID of a VirtueMart product */
	var $vmproductid = 1;
	/** @var integer Set the Item ID of VirtueMart */
	var $vmitemid = 1;
	/** @var integer Set if captcha is enabled */
	var $captchaactive = 0;
	/** @var integer Sets the access level for a form */
	var $access = 0;
	
	
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
	function getFields()
	{
		if (empty($this->id)) {
			return array();
		}
		
		$db =& $this->getDBO();
		
		$query = ' SELECT * FROM #__rwf_fields WHERE form_id ='. $db->Quote($this->id);
		$db->setQuery($query);
		return $db->loadResultArray();
	}
}
?>