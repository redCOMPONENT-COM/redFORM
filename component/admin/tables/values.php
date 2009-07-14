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
class TableValues extends JTable {
	/** @var int Primary key */
	var $id = null;
	/** @var string The value for the field */
	var $value = null;
	/** @var string Set to true if the value is published */
	var $published = null;
	/** @var string Set to true if the value is checked out */
	var $checked_out = null;
	/** @var string Time the value is checked out */
	var $checked_out_time = null;
	/** @var string The field the value is connected to */
	var $field_id = null;
	/** @var string Set the fieldtype of the field */
	var $fieldtype = null;
	/** @var string Set the order of the value */
	var $ordering = null;
	
	/**
	* @param database A database connector object
	*/
	function __construct( &$db ) {
		parent::__construct('#__rwf_values', 'id', $db );
	}
	
	/**
	 * Get the next row order
	 */
	public function getNextOrder($form_id = 0) {
		$db = JFactory::getDBO();
				
		if (!$form_id || $form_id == 0) {
			/* Find the form ID */
			$q = "SELECT form_id
				FROM #__rwf_fields
				WHERE id = ".JRequest::getInt('field_id');
			$db->setQuery($q);
			$form_id = $db->loadResult();
		}
		
		$q = "SELECT MAX(v.ordering) 
			FROM #__rwf_values v, #__rwf_fields f
			WHERE v.field_id = f.id
			AND form_id = ".$form_id;
		$db->setQuery($q);
		return $db->loadResult()+1;
	}
}
?>