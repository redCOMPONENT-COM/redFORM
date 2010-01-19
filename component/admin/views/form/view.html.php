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

jimport( 'joomla.application.component.view' );

/**
 * redFORM View
 */
class RedformViewForm extends JView {
	
	/**
	 * redFORM view display method
	 * @return void
	 **/
	function display($tpl = null) 
	{
		global $mainframe;
		
		if ($this->getLayout() == 'detailsform') {
		  $this->_displayDetails($tpl);
		  return;
		}
		else {
			$this->setLayout('editform');
		}

		$row = $this->get('Data');

		/* Get the show name option */
		$lists['showname']= JHTML::_('select.booleanlist',  'showname', 'class="inputbox"', $row->showname);

		/* Get the published option */
		$lists['published']= JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $row->published);

		/* Get the access level option */
		$lists['access'] = JHTML::_('list.accesslevel',  $row );

		/* Get the contactperson info option */
		$lists['contactpersoninform']= JHTML::_('select.booleanlist',  'contactpersoninform', 'class="inputbox"', $row->contactpersoninform);

		/* Get the contactperson post option */
		$lists['contactpersonfullpost']= JHTML::_('select.booleanlist',  'contactpersonfullpost', 'class="inputbox"', $row->contactpersonfullpost);

		/* Get the submitter info option */
		$lists['submitterinform']= JHTML::_('select.booleanlist',  'submitterinform', 'class="inputbox"', $row->submitterinform);

		/* Get the notification option */
		$lists['submitnotification']= JHTML::_('select.booleanlist',  'submitnotification', 'class="inputbox"', $row->submitnotification);

		/* Get the form expires option */
		$lists['formexpires']= JHTML::_('select.booleanlist',  'formexpires', 'class="inputbox"', $row->formexpires);

		/* Get the form expires option */
		$lists['captchaactive']= JHTML::_('select.booleanlist',  'captchaactive', 'class="inputbox"', $row->captchaactive);

		/* Get the VirtueMart option */
		$lists['virtuemartactive']= JHTML::_('select.booleanlist',  'virtuemartactive', 'class="inputbox"', $row->virtuemartactive);

		/* Check if VirtueMart is installed */
		$vmok = $this->get('VmInstalled');

		if ($vmok) {
			/* Get the VirtueMart products */
			$products = $this->get('VmProducts');
			$lists['vmproductid'] = JHTML::_('select.genericlist', $products, 'vmproductid', 'class="inputbox"', 'product_id', 'product_name', $row->vmproductid);
		}
		else $lists['vmproductid'] = '';

		/* Get the VirtueMart option */
		$lists['paymentactive']= JHTML::_('select.booleanlist',  'activatepayment', 'class="inputbox"', $row->activatepayment);
		
		// currencies
		require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'currency.php');
		$options = array(JHTML::_('select.option', '', JText::_('Select currency')));
		$options = array_merge($options, RedformHelperLogCurrency::getCurrencyOptions());
		$lists['currency'] = JHTML::_('select.genericlist', $options, 'currency', 'class="inputbox"', 'value', 'text', $row->currency);
		
		/* Set variabels */
		$this->assignRef('row', $row);
		$this->assignRef('lists', $lists);

		/* Get the toolbar */
		switch (JRequest::getCmd('task')) {
			case 'add':
				JToolBarHelper::title(JText::_( 'Add Form' ), 'redform_plus');
				break;
			default:
				JToolBarHelper::title(JText::_( 'Edit Form' ), 'redform_plus');
				break;
		}
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();

		/* Display the page */
		parent::display($tpl);
	}

	function _displayDetails($tpl = null)
	{
		/* Get competition details */
		$form = $this->get('Data');

		/* Get submitters */
		$cid = JRequest::getVar('cid');
		JRequest::setVar('form_id', $cid[0]);
		$submitters = $this->get('Submitters', 'submitters');

		/* Newsletter signup */
		$newsletter = $this->get('NewsletterSignup', 'submitters');

		/* menu */
		RedformHelper::setMenu();
		
		/* set the toolbar */
		JToolBarHelper::title(JText::_( 'Details Form' ), 'redform_details');
		JToolBarHelper::back();

    /* Set variabels */
		$this->assignRef('form', $form);
		$this->assignRef('submitters', $submitters);
		$this->assignRef('newsletter', $newsletter);

		/* Display the page */
		parent::display($tpl);

	}
}
?>
