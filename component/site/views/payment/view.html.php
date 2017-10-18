<?php
/**
 * @package    Redform.Site
 *
 * @copyright  Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');


/**
 * Front-end view for payment
 *
 * @package  Redform.Site
 * @since    1.5
 */
class RedformViewPayment extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	public function display($tpl = null)
	{
		if ($this->getLayout() == 'select' || $this->getLayout() == 'billing')
		{
			return $this->displaySelect($tpl);
		}

		if ($this->getLayout() == 'final')
		{
			return $this->displayFinal($tpl);
		}

		return parent::display($tpl);
	}

	/**
	 * Display select layout
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	private function displaySelect($tpl = null)
	{
		$params = JFactory::getApplication()->getParams('com_redform');
		$uri = JFactory::getURI();
		$document = JFactory::getDocument();
		$input = JFactory::getApplication()->input;

		$uri->delVar('task');

		$reference = $input->get('reference', '');
		$source = $input->get('source', '');

		if (empty($reference))
		{
			echo JText::_('COM_REDFORM_PAYMENT_ERROR_MISSING_REFERENCE');

			return false;
		}

		$document->setTitle($document->getTitle() . ' - ' . JText::_('COM_REDFORM'));

		$gwoptions = $this->get('GatewayOptions');

		if (!count($gwoptions))
		{
			echo JText::_('COM_REDFORM_PAYMENT_ERROR_MISSING_GATEWAY');

			return;
		}

		$lists['gwselect'] = JHTML::_('select.genericlist', $gwoptions, 'gw');

		$price    = $this->get('Price');
		$currency = $this->get('Currency');

		$this->assignRef('lists',  $lists);
		$this->assign('action',    htmlspecialchars($uri->toString()));
		$this->assign('reference', $reference);
		$this->assign('source',    $source);
		$this->assign('price',     $price);
		$this->assign('currency',  $currency);

		if ($this->getLayout() == 'billing')
		{
			$model = RModel::getFrontInstance('Billing');
			$this->item = $model->getItem();
			$this->billingform = $model->getForm();
		}

		// Analytics
		if (RdfHelperAnalytics::isEnabled())
		{
			$event = new stdclass;
			$event->category = 'payment';
			$event->action = 'display';
			$event->label = "display payment form {$reference}";
			$event->value = null;
			RdfHelperAnalytics::trackEvent($event);
		}

		return parent::display($tpl);
	}

	/**
	 * Display final layout
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	private function displayFinal($tpl = null)
	{
		$settings = JComponentHelper::getParams('com_redform');
		$document   = JFactory::getDocument();
		$document->setTitle($document->getTitle() . ' - ' . JText::_('COM_REDFORM'));

		$form = $this->get('form');
		$cart = $this->get('cart');
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

		if (!empty($form->params->get('notification_extra')))
		{
			$text .= $form->params->get('notification_extra');
		}
		elseif (!empty($settings->get('notification_extra')))
		{
			$text .= $settings->get('notification_extra');
		}

		$text = $cart->replaceTags($text);

		$this->text = $text;

		return parent::display($tpl);
	}
}
