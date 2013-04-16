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
		if ($this->getLayout() == 'final') {
			return $this->_displayFinal($tpl);
		}
		parent::display($tpl);
	}

	function _displaySelect($tpl = null)
	{
		$uri 		    = &JFactory::getURI();
		$document   = &JFactory::getDocument();

		$submit_key = JRequest::getVar('key',    '');
		$source     = JRequest::getVar('source', '');
		if (empty($submit_key)) {
			echo JText::_('COM_REDFORM_PAYMENT_ERROR_MISSING_KEY');
			return;
		}

		$document->setTitle($document->getTitle().' - '.JText::_('COM_REDFORM'));

		$gwoptions = $this->get('GatewayOptions');
		if (!count($gwoptions)) {
			echo JText::_('COM_REDFORM_PAYMENT_ERROR_MISSING_GATEWAY');
			return;
		}
		$lists['gwselect'] = JHTML::_('select.genericlist', $gwoptions, 'gw');

		$price    = $this->get('Price');
		$currency = $this->get('Currency');

		$this->assignRef('lists',  $lists);
		$this->assign('action',    htmlspecialchars($uri->toString()));
		$this->assign('key',       $submit_key);
		$this->assign('source',    $submit_key);
		$this->assign('price',     $price);
		$this->assign('currency',  $currency);

		// Analytics
		if (redFORMHelperAnalytics::isEnabled())
		{
			$event = new stdclass;
			$event->category = 'payement';
			$event->action = 'display';
			$event->label = "display payment form {$submit_key}";
			$event->value = null;
			redFORMHelperAnalytics::trackEvent($event);
		}

		parent::display($tpl);
	}

	function _displayFinal($tpl = null)
	{
		$document   = &JFactory::getDocument();
		$document->setTitle($document->getTitle().' - '.JText::_('COM_REDFORM'));

		$form = $this->get('form');
		$text = '';
		switch (JRequest::getVar('state'))
		{
			case 'accepted':
				$text = $form->paymentaccepted;
				break;
			case 'processing':
				$text = $form->paymentprocessing;
				break;
		}

		$this->assign('text',  $text);

		parent::display($tpl);
	}
}
