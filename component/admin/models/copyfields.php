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
 * Fields Model
 */
class RedformModelCopyfields extends JModel {
  
	protected $_cids = null;
	
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
   * set fields ids
   * @param array $cids
   */
  public function setCids($cids)
  {
  	$this->_cids = $cids;
  }
  
  /**
   * return name and type of the fields to copy
   * 
   * @param array $cids field ids
   * @return boolean|array of objects
   */
  public function getFields()
  {
  	if (!is_array($this->_cids) || !count($this->_cids)) {
  		return false;
  	}
  	$query = ' SELECT f.id, f.field, f.fieldtype, fo.formname ' 
  	       . ' FROM #__rwf_fields AS f ' 
  	       . ' INNER JOIN #__rwf_forms AS fo ON fo.id = f.form_id '
  	       . ' WHERE f.id IN (' . implode(',', $this->_cids).') '
  	       . ' ORDER BY f.field ASC '
  	       ;
  	$this->_db->setQuery($query);
  	$res = $this->_db->loadObjectList('id');
  	
  	return $res;
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
   * copy the fields to form
   * 
   * @param array $cids field ids
   * @param int $form_id
   * @return boolean true on success
   */
  public function copy($cids, $form_id)
  {
  	return true;
  }
}
