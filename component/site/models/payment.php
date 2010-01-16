<?php
/**
 * @version 1.0 $Id: archive.php 217 2009-06-06 20:04:26Z julien $
 * @package Joomla
 * @subpackage redform
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

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * redform Component payment Model
 *
 * @package Joomla
 * @subpackage redform
 * @since		0.9
 */
class RedFormModelPayment extends JModel
{
	var $_gateways = null;
	
	var $_submit_key = null;
	
	function __construct($config)
	{
		parent::__construct();
				
		$this->setSubmitKey(JRequest::getVar('key', ''));
	}
		
	function setSubmitKey($key)
	{
		if (!empty($key)) {
			$this->_submit_key = $key;
		}
	}
	
	/**
	 * get redform plugin payment gateways, as an array of name and helper class
	 * @return array
	 */
	function getGateways()
	{
		if (empty($this->_gateways))
		{
			JPluginHelper::importPlugin( 'redform_payment' );
	  	$dispatcher = &JDispatcher::getInstance();
	  	$gateways = array();
	  	$results = $dispatcher->trigger('onGetGateway', array(&$gateways));
	  	$this->_gateways = $gateways;
		}
  	return $this->_gateways;
	}
	
	/**
	 * return gateways as options
	 * @return array
	 */
	function getGatewayOptions()
	{
		$gw = $this->getGateways();
		
		$options = array();
		foreach ($gw as $g)
		{
			$options[] = JHTML::_('select.option', $g['name'], $g['name']);
		}
		return $options;
	}
	
	/**
	 * return total price for submissions associated to submit _key
	 * @return float
	 */
	function getPrice()
	{
		if (empty($this->_submit_key)) {
			JError::raiseError(0, JText::_('Missing key'));
			return false;
		}
		
		$query = ' SELECT price FROM #__rwf_submitters WHERE submit_key = '. $this->_db->Quote($this->_submit_key)
		            ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadResultArray();
		dump($res);
		$total = 0.0;
		foreach ($res as $p) {
			$total += $p;
		}
		return $total;
	}
	
	/**
	 * return currency of form associated to this payment
	 * @return unknown_type
	 */
	function getCurrency()
	{
		if (empty($this->_submit_key)) {
			JError::raiseError(0, JText::_('Missing key'));
			return false;
		}
		
		$query = ' SELECT f.currency FROM #__rwf_submitters AS s '
		       . ' INNER JOIN #__rwf_forms AS f on s.form_id = f.id '
		       . ' WHERE s.submit_key = '. $this->_db->Quote($this->_submit_key)
		            ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadResult();	

		return $res;
	}
}