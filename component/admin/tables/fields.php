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