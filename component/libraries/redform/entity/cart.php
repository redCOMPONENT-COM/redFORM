<?php
/**
 * @package     Redform.Library
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Cart entity.
 *
 * @since  3.0
 */
class RdfEntityCart extends RdfEntityBase
{
	/**
	 * @var RdfEntitySubmitter[]
	 */
	private $submitters;

	/**
	 * @var RdfEntityPaymentrequest[]
	 */
	private $paymentRequests;

	/**
	 * @var RdfPaymentInfointegration
	 */
	private $integrationDetails;

	/**
	 * Return instance
	 *
	 * @param   string  $reference  cart reference
	 *
	 * @return RdfEntityCart
	 */
	public function loadByReference($reference)
	{
		$table = $this->getTable();
		$table->load(array('reference' => $reference));

		if ($table->id)
		{
			$this->loadFromTable($table);
		}

		return $this;
	}

	/**
	 * Return billing info, possibly from another submission from same submitter
	 *
	 * @return RdfEntityBilling
	 */
	public function getABilling()
	{
		if (!$this->hasId())
		{
			return false;
		}

		// First check for a billing for this cart
		$billing = $this->getBilling();

		if ($billing->hasId())
		{
			return $billing;
		}

		// Check for a billing for any other cart for any of this cart submitters
		$submitters = $this->getSubmitters();

		return reset($submitters)->getASubmissionBilling();
	}

	/**
	 * Return billing info
	 *
	 * @return RdfEntityBilling
	 */
	public function getBilling()
	{
		if (!$this->hasId())
		{
			return false;
		}

		$billing = new RdfEntityBilling;
		$billing->loadByCartId($this->id);

		return $billing;
	}

	/**
	 * Get form entity
	 *
	 * @return RdfEntityForm
	 */
	public function getForm()
	{
		$submitters = $this->getSubmitters();
		$submitter = reset($submitters);

		return $submitter->getForm();
	}

	/**
	 * Get integration info
	 *
	 * @return RdfPaymentInfointegration
	 */
	public function getIntegrationInfo()
	{
		if (is_null($this->integrationDetails))
		{
			$this->integrationDetails = false;

			JPluginHelper::importPlugin('redform_integration');
			$dispatcher = JDispatcher::getInstance();

			foreach ($this->getPaymentRequests() as $pr)
			{
				if (!$pr->integration)
				{
					continue;
				}

				$integrationDetails = new RdfPaymentInfointegration;
				$dispatcher->trigger('getRFSubmissionPaymentDetailFields',
					array(
						$pr->integration,
						$pr->submit_key,
						&$integrationDetails
					)
				);

				if ($integrationDetails)
				{
					$this->integrationDetails = $integrationDetails;

					return $this->integrationDetails;
				}
			}
		}

		return $this->integrationDetails;
	}

	/**
	 * return submitters
	 *
	 * @return RdfEntityPaymentrequest[]
	 */
	public function getPaymentRequests()
	{
		if (empty($this->paymentRequests) && $this->hasId())
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('pr.*, s.integration, s.submit_key')
				->from('#__rwf_payment_request AS pr')
				->join('INNER', '#__rwf_cart_item AS ci ON ci.payment_request_id = pr.id')
				->join('INNER', '#__rwf_submitters AS s ON s.id = pr.submission_id')
				->where('ci.cart_id = ' . $this->id);

			$db->setQuery($query);
			$result = $db->loadObjectList();

			$this->paymentRequests = array_map(
				function ($item)
				{
					$instance = RdfEntityPaymentrequest::getInstance();
					$instance->bind($item);

					return $instance;
				},
				$result
			);
		}

		return $this->paymentRequests;
	}

	/**
	 * return submitters
	 *
	 * @return RdfEntitySubmitter[]
	 */
	public function getSubmitters()
	{
		if (empty($this->submitters))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('s.*')
				->from('#__rwf_submitters AS s')
				->join('INNER', '#__rwf_payment_request AS pr ON pr.submission_id = s.id')
				->join('INNER', '#__rwf_cart_item AS ci ON ci.payment_request_id = pr.id')
				->where('ci.cart_id = ' . $db->quote($this->id));

			$db->setQuery($query);
			$result = $db->loadObjectList();

			$this->submitters = array_map(
				function ($item)
				{
					$instance = RdfEntitySubmitter::getInstance();
					$instance->bind($item);

					return $instance;
				},
				$result
			);
		}

		return $this->submitters;
	}

	/**
	 * Prefill billing table
	 *
	 * @param   RedformTableBilling  &$table  table
	 *
	 * @return true if some fields were filled
	 */
	public function prefillBilling(RedformTableBilling &$table)
	{
		$table->cart_id = $this->id;

		$submitters = $this->getSubmitters();
		$asubmitter = reset($submitters);

		$submissionModel = new RdfCoreModelSubmission;
		$submission = $submissionModel->getSubmission(array($asubmitter->id));

		$fields = $submission->getFirstSubmission()->getFields();

		$data = array();

		$prefilled = false;

		foreach ($fields as $field)
		{
			if ($mapping = $field->getParam('billing_field'))
			{
				$data[$mapping] = $field->getValue();
				$prefilled = !empty($data[$mapping]);
			}
		}

		$table->bind($data);

		JPluginHelper::importPlugin('redform');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onRedformPrefillBilling', array($this->reference, &$table, &$prefilled));

		return $prefilled;
	}
}
