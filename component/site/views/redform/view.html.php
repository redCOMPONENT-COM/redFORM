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
class RedformViewRedform extends JView {
	
	function display($tpl = null) 
	{
		$mainframe = JFactory::getApplication();
				
		/* Get the VirtueMart settings */
		$vmsettings = $this->get('VmSettings');
		
		/* Set the page title */
		$document = JFactory::getDocument();
		$document->setTitle($document->getTitle().' - '.JText::_('COM_REDFORM'));
		
		/* Get the product image and description */
		$productdetails = $this->get('ProductDetails');
		
		/* Assign the necessary data */
		$this->assignRef('vmsettings', $vmsettings);
		$this->assignRef('productdetails', $productdetails);
		
		parent::display($tpl);
	}
}
?>
