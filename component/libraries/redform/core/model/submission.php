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
 * Class RdfCoreModelSubmission
 *
 * @package     Redform.Libraries
 * @subpackage  Core.Model
 * @since       3.0
 */
class RdfCoreModelSubmission extends RModel
{
	protected $submitKey;

	protected $submission;

	protected $formModel;

	protected $data;

	/**
	 * Constructor
	 *
	 * @param   array  $config  optional config
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		if ($config && isset($config['submitKey']))
		{
			$this->setSubmitKey($config['submitKey']);
		}
	}

	/**
	 * Set submit key
	 *
	 * @param   string  $key  submit key to set
	 *
	 * @return object
	 */
	public function setSubmitKey($key)
	{
		$this->submitKey = $key;

		return $this;
	}

	/**
	 * Return full submission data, optionally only for specified sids
	 *
	 * @param   array  $sids  array of sid to restrict to
	 *
	 * @return RdfCoreFormSubmission
	 */
	public function getSubmission($sids = null)
	{
		if (!$this->submission)
		{
			$submission = new RdfCoreFormSubmission;
			$submission->setSubmitKey($this->submitKey);
			$this->submission = $submission;
		}

		if (!$sids)
		{
			$sids = $this->getSids($this->submitKey);
		}

		foreach ($sids as $sid)
		{
			if (!$this->submission->getSubmissionBySid($sid))
			{
				$answers = $this->getSubSubmission($sid);
				$this->submission->addSubSubmission($answers);
			}
		}

		return $this->submission;
	}

	/**
	 * Return submission associated to single sid
	 *
	 * @param   int  $sid  submitter id
	 *
	 * @return RdfAnswers
	 */
	protected function getSubSubmission($sid)
	{
		$db = $this->_db;

		$formId = $this->getFormId();

		// Get data
		$query = $db->getQuery(true)
			->select('s.id as sid, f.*, s.price')
			->select('CASE WHEN (s.currency) THEN s.currency ELSE fo.currency END as currency')
			->from('#__rwf_forms_' . $formId . ' AS f')
			->join('INNER', '#__rwf_submitters AS s on s.answer_id = f.id')
			->join('INNER', '#__rwf_forms AS fo on fo.id = s.form_id')
			->where('s.id = ' . (int) $sid);
		$db->setQuery($query);
		$submissionsData = $db->loadObject();

		$fields = $this->getFormModel()->getFormFields();

		$subSubmission = new RdfAnswers;
		$subSubmission->setSubmitKey($this->submitKey);
		$subSubmission->setSid($sid);
		$subSubmission->setFormId($formId);
		$subSubmission->setCurrency($submissionsData->currency);

		foreach ($fields as $field)
		{
			$clonedField = clone $field;

			if (isset($submissionsData->{'field_' . $clonedField->field_id}))
			{
				$clonedField->setValueFromDatabase($submissionsData->{'field_' . $clonedField->field_id});
			}

			$subSubmission->addField($clonedField);
		}

		return $subSubmission;
	}

	/**
	 * Get sids from submit key
	 *
	 * @param   string  $submitKey  submit key
	 *
	 * @return mixed
	 */
	public function getSids($submitKey)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from('#__rwf_submitters');
		$query->where('submit_key = ' . $db->quote($submitKey));

		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * Get submit key from sid
	 *
	 * @param   int  $sid  sid
	 *
	 * @return string
	 */
	public function getSidSubmitKey($sid)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('submit_key');
		$query->from('#__rwf_submitters');
		$query->where('id = ' . $db->quote($sid));

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Get raw data from db
	 *
	 * @return mixed
	 */
	protected function getData()
	{
		if (!$this->data)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('*');
			$query->from('#__rwf_submitters');
			$query->where('submit_key = ' . $db->quote($this->submitKey));

			$db->setQuery($query);
			$this->data = $db->loadObjectList();
		}

		return $this->data;
	}

	/**
	 * Get form id
	 *
	 * @return mixed
	 */
	protected function getFormId()
	{
		$data = $this->getData();

		if ($data && count($data))
		{
			return $data[0]->form_id;
		}

		return false;
	}

	/**
	 * Return submission(s) price(s) associated to a submit_key
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return array indexed by submitter_id
	 */
	public function getSubmissionPrice($submit_key)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('s.id, s.submit_key, s.price, s.vat, s.currency');
		$query->from('#__rwf_submitters AS s');
		$query->join('INNER', '#__rwf_forms AS f ON f.id = s.form_id');
		$query->where('s.submit_key = ' . $db->q($submit_key));

		$db->setQuery($query);
		$res = $db->loadObjectList('id');

		return ($res);
	}

	/**
	 * Return Form model
	 *
	 * @return RdfCoreModelForm
	 */
	protected function getFormModel()
	{
		if (!$this->formModel)
		{
			$this->formModel = new RdfCoreModelForm($this->getFormId());
		}

		return $this->formModel;
	}
}
