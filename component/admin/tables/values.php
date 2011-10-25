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
class RedformTableValues extends JTable {
	/** @var int Primary key */
	var $id = null;
	/** @var string The value for the value */
	var $value = null;
	/** @var string The label for the value */
	var $label = null;
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
	public function getNextOrder($form_id = 0) 
	{
		$db = JFactory::getDBO();
		
		if (!$form_id) 
		{
			$field_id = JRequest::getInt('field_id', $this->field_id);
			
			if ($field_id)
			{
				/* Find the form ID */
				$q = "SELECT form_id
					FROM #__rwf_fields
					WHERE id = ".$db->Quote($field_id);
				$db->setQuery($q);
				$form_id = $db->loadResult();
			}
		}
		
		$q = ' SELECT MAX(v.ordering) '
		   . ' FROM #__rwf_values AS v '
		   . ' INNER JOIN #__rwf_fields AS f ON v.field_id = f.id '
		   . ($form_id ? ' WHERE form_id = '.$form_id : '')
		   ;
		$db->setQuery($q);
		$max = $db->loadResult();
		return $max+1;
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
	      $this->setError(JText::_('COM_REDFORM_INVALID_EMAIL_FORMAT'));
	      return false;
	    }
	  }
	  return true;
	}
	
	/**
	 * Compacts the ordering sequence of the selected records
	 *
	 * @access public
	 * @param string Additional where query to limit ordering to a particular subset of records
	 */
	function reorder( $where='' )
	{
		$k = $this->_tbl_key;

		$query = 'SELECT v.id, v.ordering '
		. ' FROM #__rwf_values AS v '
		. ' INNER JOIN #__rwf_fields AS f ON f.id = v.field_id '
		. ' INNER JOIN #__rwf_forms AS fo ON fo.id = f.form_id '
		. ' WHERE v.ordering >= 0' . ( $where ? ' AND '. $where : '' )
		. ' ORDER BY fo.id, f.ordering, v.ordering '
		;
		$this->_db->setQuery( $query );
		if (!($orders = $this->_db->loadObjectList()))
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		// compact the ordering numbers
		for ($i=0, $n=count( $orders ); $i < $n; $i++)
		{
			if ($orders[$i]->ordering >= 0)
			{
				if ($orders[$i]->ordering != $i+1)
				{
					$orders[$i]->ordering = $i+1;
					$query = 'UPDATE '.$this->_tbl
					. ' SET ordering = '. (int) $orders[$i]->ordering
					. ' WHERE '. $k .' = '. $this->_db->Quote($orders[$i]->$k)
					;
					$this->_db->setQuery( $query);
					$this->_db->query();
				}
			}
		}

		return true;
	}
}
?>