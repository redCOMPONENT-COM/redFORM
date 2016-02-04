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
	 * @return void
	 */
	public function prefillBilling(RedformTableBilling &$table)
	{
		$submitters = $this->getSubmitters();
		$asubmitter = reset($submitters);

		$submissionModel = new RdfCoreModelSubmission;
		$submission = $submissionModel->getSubmission(array($asubmitter->id));

		$fields = $submission->getFirstSubmission()->getFields();

		$data = array();

		foreach ($fields as $field)
		{
			if ($mapping = $field->getParam('billing_field'))
			{
				$data[$mapping] = $field->getValue();
			}
		}

		$table->bind($data);

		JPluginHelper::importPlugin('redform');
		$dispatcher = JDispatcher::getInstance();

		$prefilled = false;
		$dispatcher->trigger('onRedformPrefillBilling', array($this->reference, &$table, &$prefilled));
	}
}
