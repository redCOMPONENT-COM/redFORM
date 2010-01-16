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
}