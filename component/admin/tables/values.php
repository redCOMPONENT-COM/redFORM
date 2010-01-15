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
	/** @var float option price */
	var $price = null;
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
	
	public function check()
	{
		// get fieldtype
		$q = ' SELECT fieldtype	FROM #__rwf_fields WHERE id = '.$this->_db->Quote($this->field_id);
		$this->_db->setQuery($q, 0, 1);
		
		$fieldtype = $this->_db->loadResult(); 
	  if ($fieldtype == 'recipients') 
	  {
  	  jimport( 'joomla.mail.helper' );
	    if (! JMailHelper::isEmailAddress($this->value) ) 
	    {
	      $this->setError(JText::_('INVALID EMAIL FORMAT'));
	      return false;
	    }
	  }
	  return true;
	}
}
?>