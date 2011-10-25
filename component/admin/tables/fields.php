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
class RedformTableFields extends JTable {
	/** @var int Primary key */
	var $id = null;
	/** @var string field name */
	var $field = null;
	/** @var string field header for tables */
	var $field_header = null;
	/** @var string field type */
	var $fieldtype = null;
	/** @var int published state */
	var $published = null;
	/** @var int id of user having checked out the item */
	var $checked_out = null;
	/** @var string */
	var $checked_out_time = null;
	/** @var int */
	var $form_id = null;
	/** @var int */
	var $ordering = null;
	/** @var int */
	var $validate = null;
	/** @var int */
	var $unique = null;
	/** @var int */
	var $readonly = 0;
	/** @var string The default value for a field */
	var $default = null;
	/** @var string The tooltip for a field */
	var $tooltip = null;
	/** @var string linked redmember field db name */
	var $redmember_field = null;	
	/** @var string custom params */
	var $params = null;
	
	/**
	* @param database A database connector object
	*/
	function __construct( &$db ) {
		parent::__construct('#__rwf_fields', 'id', $db );
	}

	function bind($array, $ignore = '')
	{
		if (key_exists( 'params', $array ) && is_array( $array['params'] ))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}
		return parent::bind($array, $ignore);
	}
	
	function check()
	{
		if (empty($this->fieldtype)) {
			$this->setError(JText::_('COM_REDFORM_Field_type_is_required'));
			return false;
		}
		if (empty($this->form_id)) {
			$this->setError(JText::_('COM_REDFORM_Form_is_required'));
			return false;
		}
		if (empty($this->field)) {
			$this->setError(JText::_('COM_REDFORM_Field_name_is_required'));
			return false;
		}
		return true;
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
    
    $query = ' SELECT * FROM #__rwf_values '
           . ' WHERE field_id ='. $db->Quote($this->id)
           . ' ORDER BY ordering '
           ;
    $db->setQuery($query);
    return $db->loadResultArray();
  }
  
}
?>