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

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport( 'joomla.application.component.model' );

/**
 * Submitters import  Model
 */
class RedformModelImportsubmitters extends JModel {

	/**
	 * form id
	 * @var int
	 */
	protected $_form_id = 0;
	
	/**
	 * form fields
	 * @var array
	 */
	protected $_fields = null;
	
  /**
   * Constructor
   *
   * @since 0.9
   */
  public function __construct()
  {
    parent::__construct();
  }
  
  /**
   * form id setter
   * 
   * @param int $id
   */
  public function setFormId($id)
  {
  	$this->_form_id = intval($id);
  }
  
  /**
   * return forms as options
   * 
   * @return array
   */
  public function getFormsOptions()
  {
  	$query = "SELECT id AS value, formname AS text FROM #__rwf_forms";
  	$where = array();
  	switch ($this->getState('form_state'))
  	{
  		case 1:
  			$where[] = ' published >= 0 ';
  			break;
  		case -1:
  			$where[] = ' published < 0 ';
  			break;
  	}
  	if (count($where)) {
  		$query .= ' WHERE '.implode(' AND ', $where);
  	}
  	$this->_db->setQuery($query);
  	return $this->_db->loadObjectList();
  }
  
  /**
   * get form fields
   * 
   * @return array
   */
  public function getFields()
  {
  	if (empty($this->_fields))
  	{
	  	$query = ' SELECT f.id, f.*  ' 
	  	       . ' FROM #__rwf_fields AS f ' 
	  	       . ' WHERE f.form_id = ' . $this->_db->Quote($this->_form_id);
	  	$this->_db->setQuery($query);
	  	$fields = $this->_db->loadObjectList('id');
	  	
	  	foreach ($fields as $k => $f)
	  	{
	  		$fields[$k]->options = array();
	  	}
	  		  	
	  	$query = ' SELECT v.*  '
	  		  	       . ' FROM #__rwf_values AS v ' 
	  		  	       . ' INNER JOIN #__rwf_fields AS f ON f.id = v.field_id ' 
	  		  	       . ' WHERE f.form_id = ' . $this->_db->Quote($this->_form_id);
	  	$this->_db->setQuery($query);
	  	$values = $this->_db->loadObjectList();
	  	
	  	foreach ((array)$values as $v)
	  	{
	  		$fields[$v->field_id]->options[] = $v;
	  	}
	  	$this->_fields = $fields;
  	}
  	return $this->_fields;
  }
  
  /**
   * get field object asssociated to name in current form
   * 
   * @param string $name
   * @return object
   */
  public function getFieldByName($name)
  {
  	$fields = $this->getFields();
  	foreach ($fields as $f)
  	{
  		if ($f->field == $name || $f->field_header == $name) {
  			return $f;
  		}
  	}
  	return false;
  }
  
  /**
   * import records
   * 
   * @param array $records
   * @return array
   */
  public function import($records)
  {
  	$fields = $this->getFields();
  	foreach ($records as $record)
  	{
  		$query_fields = array();
  		foreach ($record as $name => $value)
  		{
	  		if (strpos($name, 'field_') === 0) 
	  		{
	  			$fid = substr($name, 6);
	  			$field = $fields[$fid];
	  			if (count($field->options)) {
	  				$value = str_replace(";", '~~~', $value);
	  			}
	  			$query_fields[] = $name.' = '. $this->_db->Quote($value);
	  		}
  		}
  		
  		if (!count($query_fields)) {
  			$this->setError(JText::_('COM_REDFORM_SUBMITTER_NO_FORM_FIELD'));
  			return false;
  		}
  		$query = ' INSERT INTO #__rwf_forms_'.$this->_form_id
  		       . ' SET '. implode(', ', $query_fields)
  		       ;
  		       
  		$this->_db->setQuery($query);
  		if (!$this->_db->query()) {
  			$this->setError($this->_db->getErrorMsg());
  			return false;
  		}
  		
  		$submitter = JTable::getInstance('submitters', 'redformtable');
  		if (isset($record[JText::_('COM_REDFORM_Submission_date')])) {
  			$submitter->submission_date = strftime('%F %T', strtotime($record[JText::_('COM_REDFORM_Submission_date')]));
  		}
  		else {
  			$submitter->submission_date = strftime('%F %T');
  		}
  		$submitter->submit_key = uniqid();
  		$submitter->form_id = $this->_form_id;
  		$submitter->answer_id = $this->_db->insertid();
  		$submitter->price = $this->_calcPrice($record);
  		
  		if (!$submitter->check()) {
  			$this->setError($submitter->getError());
  			return false;
  		}
  		if (!$submitter->store()) {
  			$this->setError($submitter->getError());
  			return false;
  		}
  	}
  	return true;
  }
  
  /**
   * compute price based for the record
   * 
   * @param array $record
   * @return float
   */
  function _calcPrice($record)
  {
  	$fields = $this->getFields();
  	$price = 0.0;
  	foreach ($fields as $f)
  	{
  		if ($f->fieldtype == 'price') 
  		{
  			if (count($f->options)) {
  				$price += $f->options[0]->price;
  			}
  			else if (isset($record['field_'.$f->id])) {
  				$price += floatval($record['field_'.$f->id]);
  			}
  			continue;
  		}
  		else if (!count($f->options)) {
  			continue;
  		}
  		
  		if (!isset($record['field_'.$f->id])) {
  			continue;
  		}
  		// there can be multiple options selected
  		$answer = explode(";", $record['field_'.$f->id]);
  		foreach ((array) $answer as $val)
  		{
  			foreach ($f->options as $opt)
  			{
  				if ($opt->value == $val) {
  					$price += $opt->price;
  					break;
  				}
  			}
  		}
  	}
  	return $price;
  }
}
