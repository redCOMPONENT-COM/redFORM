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
	
	var $_form = null;
	
	var $_submitters = null;
	
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
		$form = $this->getForm();

		return $form->currency;
	}
	
	/**
	 * return gateway helper
	 * @param string name
	 * @return object or false if no corresponding gateway
	 */
	function getGatewayHelper($name)
	{
		$gw = $this->getGateways();
		foreach ($gw as $g)
		{
			if (strcasecmp($g['name'], $name) == 0) {
				return $g['helper'];
			}
		}
		RedformHelperLog::simpleLog('NOTIFICATION GATEWAY NOT FOUND'.': '.$name);
		
		return false;
	}
	
	/**
	 * returns form associated to submit_key
	 * @return object
	 */
	function getForm()
	{
		if (empty($this->_submit_key)) {
			JError::raiseError(0, JText::_('Missing key'));
			return false;
		}
		
		if (empty($this->_form))
		{
			$query = ' SELECT f.* '
			       . ' FROM #__rwf_submitters AS s '
			       . ' INNER JOIN #__rwf_forms AS f ON f.id = s.form_id '
			       . ' WHERE s.submit_key = '. $this->_db->Quote($this->_submit_key)
			            ;
			$this->_db->setQuery($query, 0, 1);
			$this->_form = $this->_db->loadObject();
		}
		return $this->_form;
	}
	
	function getSubmitters()
	{
		if (empty($this->_submit_key)) {
			JError::raiseError(0, JText::_('Missing key'));
			return false;
		}
		
		if (empty($this->_submitters))
		{
			$query = ' SELECT s.* '
			       . ' FROM #__rwf_submitters AS s '
			       . ' WHERE s.submit_key = '. $this->_db->Quote($this->_submit_key)
			            ;
			$this->_db->setQuery($query);
			$this->_submitters = $this->_db->loadObjectList();
		}
		return $this->_submitters;		
	}
	
	/**
	 * provides information for process function of helpers (object id, title, etc...)
	 * @param $key
	 * @return unknown_type
	 */
	function getPaymentDetails($key)
	{
		$submitters = $this->getSubmitters();
		if (!count($submitters)) {
			return false;
		}
		$asub = current($submitters);
		$form = $this->getForm();
		
		$obj = new stdclass;
		$obj->integration = $asub->integration;
		$obj->form        = $form->formname ;
		$obj->form_id     = $form->id;
		$obj->key         = $key;
		switch ($asub->integration)
		{
			case 'redevent':
				$event = $this->getEventAttendee($key);
				$obj->title = JText::_('Event registration').': '.$event->title.' @ '.$event->venue. ', '. strftime('%x', strtotime($event->dates)).' '.($event->times && $event->times != '00:00:00' ? $event->times : '');
				$obj->uniqueid = $event->uniqueid;
				break;
			default:
				if (JRequest::getVar('paymenttitle')) {
					$obj->title = JRequest::getVar('paymenttitle');					
				}
				else {
					$obj->title = JText::_('Form submission').': '.$form->title;
				}
				$obj->uniqueid = $key;
				break;
		}
		return $obj;
	}
	
	function getEventAttendee($key)
	{
		$query = ' SELECT r.id as attendee_id, e.title, e.course_code, r.xref, v.venue, x.* '
		              . ' FROM #__redevent_register AS r '
		              . ' INNER JOIN #__redevent_event_venue_xref AS x on x.id = r.xref '
		              . ' INNER JOIN #__redevent_events AS e on e.id = x.eventid '
		              . ' INNER JOIN #__redevent_venues AS v on v.id = x.venueid '
		              . ' WHERE r.submit_key = '. $this->_db->Quote($key)
		              ;
		$this->_db->setQuery($query, 0, 1);
		$res = $this->_db->loadObject();
		if ($res) {
			$res->uniqueid = $res->course_code.'-'.$res->xref.'-'.$res->attendee_id;
		}
		return $res;
	}
}