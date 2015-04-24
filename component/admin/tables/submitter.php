<?php
/**
 * @package     Redform.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Submitter table.
 *
 * @package     Redshopb.Backend
 * @subpackage  Tables
 * @since       2.5
 */
class RedformTableSubmitter extends RTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'rwf_submitters';

	/** @var int Primary key */
	public $id = null;

	/** @var string The form ID */
	public $form_id = null;

	/** @var string Date and time form was submitted */
	public $submission_date = null;

	/** @var string ip with which form was submitted */
	public $submission_ip;

	/** @var string Date and time form was confirmed */
	public $confirmed_date;

	/** @var string ip with which form was confirmed */
	public $confirmed_ip;

	/** @var string type of confirmation (email, sms, etc...) */
	public $confirmed_type = 'email';

	/** @var string integration key */
	public $integration = null;

	/** @var int The cross reference ID of the redEVENT event/venue/date */
	public $xref = null;

	/** @var int key to specific form table entry */
	public $answer_id = null;

	/** @var bool If the submitter wants to be signed up to the newsletter  */
	public $submitternewsletter = null;

	/** @var string Holds the serialized post data from the forms  */
	public $rawformdata = null;

	/** @var int Unique key that identifies single registrations */
	public $submit_key = null;

	public $uniqueid = null;

	public $price = null;

	public $vat = null;

	public $currency = null;

	/**
	 * Deletes this row in database (or if provided, the row of key $pk)
	 *
	 * @param   mixed  $pk     An optional primary key value to delete.  If not set the instance property value is used.
	 * @param   bool   $force  force deletion, even if integration
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($pk = null, $force = false)
	{
		// Before delete
		if (!$force && $this->isIntegrationSubmission($pk))
		{
			$this->setError(JText::_('COM_REDFORM_CANNOT_DELETE_INTEGRATION_SUBMISSION'));

			return false;
		}

		return parent::delete($pk);
	}

	/**
	 * Called before delete().
	 *
	 * @param   mixed  $pk  an optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 */
	protected function beforeDelete($pk = null)
	{
		if (!parent::beforeDelete($pk))
		{
			return false;
		}

		return $this->doDeleteAnswers($pk);
	}

	/**
	 * Delete answers associatied to submission
	 *
	 * @param   mixed  $pk  an optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 */
	private function doDeleteAnswers($pk = null)
	{
		$db = JFactory::getDbo();

		$pks = $this->sanitizeInPk($pk);
		$formId = $this->getAssociatedFormId($pks);

		//@TODO: convert to JDatabaseQuery
		// Delete answers
		$query = 'DELETE a FROM #__rwf_forms_' . $formId . ' AS a'
			. ' INNER JOIN #__rwf_submitters AS s ON s.answer_id = a.id'
			. ' WHERE s.id IN (' . $pks . ')';
		$db->setQuery($query);

		if (!$db->execute())
		{
			$msg = JText::_('COM_REDFORM_A_PROBLEM_OCCURED_WHEN_DELETING_THE_ANSWERS');
			$this->setError($msg);
			RdfHelperLog::simpleLog($msg . ': ' . $db->getError());

			return false;
		}

		return true;
	}

	/**
	 * Return true if one of the submission at least has integration set
	 *
	 * @param   array  $pks  int id of submissions
	 *
	 * @return bool
	 */
	protected function isIntegrationSubmission($pks = null)
	{
		$pks = $this->sanitizeInPk($pks);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('count(*)')
			->from('#__rwf_submitters AS s')
			->where('s.id IN (' . $pks . ') ')
			->where('CHAR_LENGTH(s.integration) > 0 ');

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res;
	}

	/**
	 * Get sanitized pk(s) for 'IN' sql condition
	 *
	 * @param   mixed  $pk  array or int
	 *
	 * @return string
	 *
	 * @throws LogicException
	 */
	protected function sanitizeInPk($pk = null)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Received an array of ids?
		if (is_array($pk))
		{
			// Sanitize input.
			JArrayHelper::toInteger($pk);
			$pk = RHelperArray::quote($pk);
			$pk = implode(',', $pk);
		}

		$pk = (is_null($pk)) ? $this->$k : $pk;

		// If no primary key is given, throw exception.
		if ($pk === null)
		{
			throw new LogicException('id is required');
		}

		return $pk;
	}

	/**
	 * Return form id associated to submission
	 *
	 * @param   int  $pk  submission ids
	 *
	 * @return int
	 */
	private function getAssociatedFormId($pk)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('form_id')
			->from('#__rwf_submitters')
			->where('id IN (' . $pk . ')');
		$db->setQuery($query);

		$formId = $db->loadResult();

		return $formId;
	}
}
