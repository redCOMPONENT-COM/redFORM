<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Core.Model
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Class RdfCoreModel Submission price
 *
 * This class deals with the creation of necessary object for submission payment
 *
 * @package     Redform.Libraries
 * @subpackage  Core.Model
 * @since       3.0
 */
class RdfCoreModelSubmissionprice extends RModel
{
	/**
	 * @var RdfAnswers
	 */
	protected $answers;

	/**
	 * Data saved to db
	 *
	 * @var object
	 */
	protected $submission;

	/**
	 * Constructor
	 *
	 * @param   array  $config  optional config
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		if ($config && isset($config['answers']))
		{
			$this->setAnswers($config['answers']);
		}
	}

	/**
	 * Set answers
	 *
	 * @param   RdfAnswers  $answers  answers
	 *
	 * @return object
	 */
	public function setAnswers($answers)
	{
		$this->answers = $answers;

		return $this;
	}

	/**
	 * Update submission price and price items
	 *
	 * @return bool
	 */
	public function updatePrice()
	{
		$params = JComponentHelper::getParams('com_redform');

		$price = $this->answers->getPrice();
		$vat = $this->answers->getVat();

		if (!$params->get('allow_negative_total', 1))
		{
			$price = max(array(0, $price));
			$vat = max(array(0, $vat));
		}

		if (!$price)
		{
			return true;
		}

		$db = $this->_db;
		$query = $db->getQuery(true);

		$row = RTable::getAdminInstance('submitter', array(), 'com_redform');
		$row->load($this->answers->sid);
		$row->price = $price;
		$row->vat = $vat;
		$row->currency = $this->answers->currency;

		$query->update('#__rwf_submitters');
		$query->set('price = ' . $db->quote($price));
		$query->set('vat = ' . $db->quote($vat));
		$query->set('currency = ' . $db->quote($this->answers->currency));
		$query->where('id = ' . $db->Quote($this->answers->sid));
		$db->setQuery($query);

		$row->store();

		$this->submission = $row;

		$this->updateSubmissionPriceItems();

		if ($price && !$this->isPaid())
		{
			$this->updatePaymentRequest();
		}

		return true;
	}

	/**
	 * create Submission Price Items
	 *
	 * @return void
	 */
	private function updateSubmissionPriceItems()
	{
		$this->deletePreviousPriceItems();

		foreach ($this->answers->getFields() as $field)
		{
			$this->createSubmissionPriceItem($field);
		}
	}

	/**
	 * create Submission Price Item from field
	 *
	 * @param   RdfRfield  $field  field
	 *
	 * @return void
	 */
	private function createSubmissionPriceItem($field)
	{
		if (!$price = $field->getPrice())
		{
			return;
		}

		$row = RTable::getAdminInstance('Submissionpriceitem', array(), 'com_redform');

		$row->submission_id = $this->answers->sid;
		$row->sku = $field->getSku();
		$row->label = $field->name;
		$row->price = round($price, RHelperCurrency::getPrecision($this->answers->getCurrency()));
		$row->vat = round($field->getVat(), RHelperCurrency::getPrecision($this->answers->getCurrency()));

		if (!$row->store())
		{
			throw new RuntimeException('Couldn\'t create submission price items');
		}
	}

	/**
	 * Delete previous price items for this submission
	 *
	 * @return void
	 */
	private function deletePreviousPriceItems()
	{
		$query = $this->_db->getQuery(true);

		$query->delete('#__rwf_submission_price_item')
			->where('submission_id = ' . $this->answers->sid);

		$this->_db->setQuery($query);

		if (!$this->_db->execute())
		{
			throw new RuntimeException('Couldn\'t delete submission price items');
		}
	}

	/**
	 * Update Payment Request
	 *
	 * @return void
	 */
	private function updatePaymentRequest()
	{
		$this->deletePreviousPaymentRequests();

		$date = JFactory::getDate();
		$row = RTable::getAdminInstance('paymentrequest', array(), 'com_redform');
		$row->submission_id = $this->submission->id;
		$row->created = $date->toSql();
		$row->price = $this->submission->price;
		$row->vat = $this->submission->vat;
		$row->currency = $this->submission->currency;

		$row->store();

		// Now create payment request items
		$query = ' INSERT INTO #__rwf_payment_request_item (payment_request_id, sku, label, price, vat) '
			. ' SELECT ' . $row->id . ', sku, label, price, vat '
			. ' FROM #__rwf_submission_price_item '
			. ' WHERE submission_id = ' . $this->answers->sid;

		$this->_db->setQuery($query);
		$this->_db->execute();
	}

	/**
	 * delete Previous Payment Requests
	 *
	 * @return void
	 */
	private function deletePreviousPaymentRequests()
	{
		$query = $this->_db->getQuery(true);

		$query->delete('#__rwf_payment_request')
			->where('submission_id = ' . $this->answers->sid)
			->where('paid = 0');

		$this->_db->setQuery($query);

		if (!$this->_db->execute())
		{
			throw new RuntimeException('Couldn\'t delete submission price items');
		}
	}

	/**
	 * Check if is paid
	 *
	 * @TODO: it might be better to check if the sum of all currently paid payment request matches submission total
	 *
	 * @return string
	 */
	private function isPaid()
	{
		$query = $this->_db->getQuery(true);

		$query->select('pr.id')
			->from('#__rwf_payment_request AS pr')
			->where('pr.submission_id = ' . $this->answers->sid)
			->where('pr.paid = 0');

		$this->_db->setQuery($query);

		return $this->_db->loadResult() ? false : true;
	}
}
