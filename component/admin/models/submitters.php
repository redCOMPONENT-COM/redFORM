<?php
/**
 * @package     Redform.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Submitters Model
 *
 * @package     Redform.Backend
 * @subpackage  Models
 * @since       2.5
 */
class RedformModelSubmitters extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_submitters';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'field_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 */
	public function __construct($config = array())
	{

		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 's.id',
				'form_id', 's.form_id',
				'date', 's.date',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$formId = $this->getUserStateFromRequest($this->context . '.filter.formId', 'filter.form_id', $this->getDefaultFormId(), 'int');
		$this->setState('filter.form_id', $formId);

		parent::populateState('s.id', 'desc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return string A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.form_id');
		$id	.= ':'.$this->getState('filter.from');
		$id	.= ':'.$this->getState('filter.to');

		return parent::getStoreId($id);
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   11.1
	 */
	protected function getListQuery()
	{
		$form_id = $this->getState('filter.form_id');

		$subPayment = "SELECT MAX(id) as id, submit_key FROM #__rwf_payment GROUP BY submit_key";

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('s.submission_date, s.form_id, s.id AS sid, f.formname, s.price, s.currency, s.submit_key');
		$query->select('s.integration, s.xref');
		$query->select('f.formname');
		$query->select('p.status, p.paid');
		$query->select('u.*');
		$query->from('#__rwf_submitters AS s');
		$query->join('INNER', '#__rwf_forms AS f ON s.form_id = f.id');
		$query->join('INNER', '#__rwf_forms_' . $form_id . ' AS u ON s.answer_id = u.id');
		$query->join('LEFT', '(' . $subPayment . ') AS latest_payment ON latest_payment.submit_key = s.submit_key');
		$query->join('LEFT', '#__rwf_payment AS p ON p.id = latest_payment.id');
		$query->where("s.form_id = " . $form_id);

		if ($from = $this->getState('filter.from'))
		{
			$date = JFactory::getDate($from)->toSql();
			$query->where('s.submission_date >= ' . $db->quote($date));
		}

		if ($to = $this->getState('filter.to'))
		{
			$date = JFactory::getDate($to)->toSql();
			$query->where('s.submission_date <= ' . $db->quote($date));
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 's.submission_date');
		$orderDirn = $this->state->get('list.direction', 'desc');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	protected function getDefaultFormId()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from('#__rwf_forms');
		$query->order('id');

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res;
	}

	public function getFormInfo($id = null)
	{
		if ($id == null)
		{
			$id = $this->getState('filter.form_id');
		}

		if ($id)
		{
			$query = ' SELECT id, formname, activatepayment, currency FROM #__rwf_forms WHERE id = ' . $this->_db->Quote($id);
			$this->_db->setQuery($query);

			return $this->_db->loadObject();
		}
		else
		{
			return false;
		}
	}

	public function getFields()
	{
		$db = JFactory::getDBO();
		$form_id = $this->getState('filter.form_id');

		$query = ' SELECT f.id, f.field '
			. '      , CASE WHEN (CHAR_LENGTH(f.field_header) > 0) THEN f.field_header ELSE f.field END AS field_header '
			. ' FROM #__rwf_fields AS f '
			. ' WHERE f.fieldtype <> "info" ';

		if ($form_id)
		{
			$query .= ' AND form_id = ' . $db->Quote($form_id);
		}

		$query .= "GROUP BY f.id
				ORDER BY f.ordering ";

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Deletes one or more submitters
	 *
	 * @param array   id of submitters records to delete
	 * @param boolean force deletion of integration rows
	 */
	public function delete($cid, $force = false)
	{
		$mainframe = JFactory::getApplication();
		$database = JFactory::getDBO();
		JArrayHelper::toInteger($cid);

		if (!is_array($cid) || count($cid) < 1)
		{
			$mainframe->enqueueMessage(JText::_('COM_REDFORM_No_submitter_found_to_delete'));
			return false;
		}

		if (count($cid))
		{
			$cids = ' s.id IN (' . implode(',', $cid) . ') ';

			// first, check that there is no integration (xref is then > 0) among these 'submitter'
			if (!$force)
			{
				$query = ' SELECT COUNT(*) FROM #__rwf_submitters AS s WHERE ' . $cids . ' AND CHAR_LENGTH(s.integration) > 0 ';
				$database->setQuery($query);
				$res = $database->loadResult();
				if ($res)
				{
					$msg = JText::_('COM_REDFORM_CANNOT_DELETE_INTEGRATION_SUBMISSION');
					$this->setError($msg);
					JError::raiseWarning(0, $msg);
					return false;
				}
			}

			// first delete the answers
			$query = ' DELETE a.* '
				. ' FROM #__rwf_submitters AS s '
				. ' INNER JOIN #__rwf_forms_' . JRequest::getInt('form_id') . ' AS a ON s.answer_id = a.id '
				. ' WHERE ' . $cids;
			$this->_db->setQuery($query);
			$res = $this->_db->loadObjectList();

			if (!$database->query())
			{
				$mainframe->enqueueMessage(JText::_('COM_REDFORM_A_problem_occured_when_deleting_the_answers'));
				RdfHelperLog::simpleLog(JText::_('COM_REDFORM_A_problem_occured_when_deleting_the_answers') . ': ' . $database->getErrorMsg());
				return false;
			}

			/* then delete the submitters */
			$query = ' DELETE s.* FROM #__rwf_submitters AS s '
				. ' WHERE ' . $cids
				. '	AND s.form_id = ' . JRequest::getInt('form_id');
			$database->setQuery($query);

			if (!$database->query())
			{
				$mainframe->enqueueMessage(JText::_('COM_REDFORM_A_problem_occured_when_deleting_the_submitter'));
				RdfHelperLog::simpleLog(JText::_('COM_REDFORM_A_problem_occured_when_deleting_the_submitter') . ': ' . $database->getErrorMsg());
				return false;
			}

			if (count($cid) > 1)
			{
				$mainframe->enqueueMessage(JText::_('COM_REDFORM_Submitters_have_been_deleted'));
			}
			else
			{
				$mainframe->enqueueMessage(JText::_('COM_REDFORM_Submitter_has_been_deleted'));
			}
		}
		return JText::_('COM_REDFORM_Removal_succesfull');
	}
}
