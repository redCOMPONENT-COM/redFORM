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
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 */
class RedformViewPayment extends JView {
	
	function display($tpl = null) 
	{		
		if ($this->getLayout() == 'select') {
			return $this->_displaySelect($tpl);
		}
		parent::display($tpl);
	}
	
	function _displaySelect($tpl = null) 
	{
		$uri 		    = &JFactory::getURI();
		$document   = &JFactory::getDocument();
		
		$submit_key = JRequest::getVar('key', '');
		if (empty($submit_key)) {
			echo Jtext::_('PAYMENT ERROR MISSING KEY');
			return;
		}
		
		$document->setTitle($document->getTitle().' - '.JText::_('redFORM'));
		
		$gwoptions = $this->get('GatewayOptions');
		if (!count($gwoptions)) {
			echo Jtext::_('PAYMENT ERROR MISSING GATEWAY');
			return;
		}
		$lists['gwselect'] = JHTML::_('select.genericlist', $gwoptions, 'gw');
		
		$price    = $this->get('Price');
		$currency = $this->get('Currency');
		
		$this->assignRef('lists',  $lists);		
    $this->assign('action',    $uri->toString());
    $this->assign('key',       $submit_key);
    $this->assign('price',     $price);
    $this->assign('currency',  $currency);
    
		parent::display($tpl);
	}
}
?>
