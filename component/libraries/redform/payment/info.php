<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Payment
 *
 * @copyright   Copyright (C) 2012 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Helper for payment details info for gateway
 *
 * @package     Redform.Libraries
 * @subpackage  Payment
 * @since       3.0
 */
class RdfPaymentInfo
{
	/**
	 * @var Object
	 */
	protected $paymentDetailFields;

	/**
	 * @var RdfEntityCart
	 */
	protected $cart;

	/**
	 * Constructor
	 *
	 * @param   RdfEntityCart  $cart  cart
	 */
	public function __construct($cart)
	{
		$this->cart = $cart;
	}

	/**
	 * is utilized for reading data from inaccessible members.
	 *
	 * @param   string  $name  property to get
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'integration':
				return $this->getASubmitter()->integration;

			case 'form':
			case 'formname':
				return $this->getForm()->formname;

			case 'form_id':
				return $this->getForm()->id;

			case 'key':
				return $this->cart->reference;

			case 'currency':
				return $this->cart->currency;

			case 'submit_key':
				return $this->getASubmitter()->submit_key;

			case 'title':
				return $this->getPaymentInfointegration()->title;

			case 'adminDesc':
				return $this->getPaymentInfointegration()->adminDesc;

			case 'uniqueid':
				return $this->getPaymentInfointegration()->uniqueid;

			case 'processIntroText':
				if (isset($this->getPaymentInfointegration()->paymentIntroText))
				{
					return $this->getPaymentInfointegration()->paymentIntroText;
				}

				return false;
		}
	}

	/**
	 * Get associated cart
	 *
	 * @return RdfEntityCart
	 */
	public function getCart()
	{
		return $this->cart;
	}

	/**
	 * Return submitters
	 *
	 * @return RdfEntitySubmitter[]
	 */
	protected function getSubmitters()
	{
		return $this->cart->getSubmitters();
	}

	/**
	 * Return a submitter
	 *
	 * @return RdfEntitySubmitter
	 */
	protected function getASubmitter()
	{
		if ($submitters = $this->getSubmitters())
		{
			return current($submitters);
		}

		return false;
	}

	/**
	 * Return form entity
	 *
	 * @return RdfEntityForm
	 */
	protected function getForm()
	{
		return $this->getASubmitter()->getForm();
	}

	/**
	 * Return payment info from integration
	 *
	 * @return null|Object|RdfPaymentInfointegration
	 */
	protected function getPaymentInfointegration()
	{
		if (!$this->paymentDetailFields)
		{
			JPluginHelper::importPlugin('redform_integration');
			$dispatcher = JDispatcher::getInstance();

			// More fields with integration
			$paymentDetailFields = new RdfPaymentInfointegration;
			$dispatcher->trigger('getRFSubmissionPaymentDetailFields', array($this->integration, $this->submit_key, &$paymentDetailFields));

			if (!$paymentDetailFields)
			{
				$uniqueid = $this->getForm()->id . '-' . $this->getASubmitter()->id . '-' . $this->cart->reference;

				if ($title = JFactory::getApplication()->input->get('paymenttitle'))
				{
					$paymentDetailFields->title = $title;
				}
				else
				{
					$paymentDetailFields->title = JText::_('COM_REDFORM_Form_submission') . ': ' . $this->form . '(' . $uniqueid . ')';
				}

				$paymentDetailFields->adminDesc = $paymentDetailFields->title;
				$paymentDetailFields->uniqueid = $uniqueid;
			}

			$this->paymentDetailFields = $paymentDetailFields;
		}

		return $this->paymentDetailFields;
	}
}
