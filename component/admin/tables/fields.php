<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 *
 * Fields table
 */

/* No direct access */
defined('_JEXEC') or die('Restricted access');

/**
 */
class TableFields extends JTable {
	/** @var int Primary key */
	var $id = null;
	/** @var string The field for the competition */
	var $field = null;
	/** @var string The field for the competition */
	var $published = null;
	/** @var string The field for the competition */
	var $checked_out = null;
	/** @var string The field for the competition */
	var $checked_out_time = null;
	/** @var string The field for the competition */
	var $form_id = null;
	/** @var integer The order of the field for the competition */
	var $ordering = 0;
	/** @var boolean Check if the field should be validated */
	var $validate = null;
	/** @var boolean Check if the field should be unique */
	var $unique = null;
	/** @var string The tooltip for a field */
	var $tooltip = null;
	
	/**
	* @param database A database connector object
	*/
	function __construct( &$db ) {
		parent::__construct('#__rwf_fields', 'id', $db );
	}
	

  /**
   * returns values records ids associated to this field
   *
   * @return array
   */
  function getValues()
  {
    if (empty($this->id)) {
      return array();
    }
    
    $db =& $this->getDBO();
    
    $query = ' SELECT * FROM #__rwf_values WHERE field_id ='. $db->Quote($this->id);
    $db->setQuery($query);
    return $db->loadResultArray();
  }
}
?>