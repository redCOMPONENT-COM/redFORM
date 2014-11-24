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
				'submission_date', 's.submission_date',
				'confirmed_date', 's.confirmed_date',
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
		parent::populateState('s.id', 'desc');

		// Receive & set filters
		if ($filters = JFactory::getApplication()->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array'))
		{
			foreach ($filters as $name => $value)
			{
				if ($name == 'form_id')
				{
					$this->setState('filter.' . $name, $value ? $value : $this->getDefaultFormId());
				}
			}
		}
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
		$id .= ':' . $this->getState('filter.form_id');
		$id .= ':' . $this->getState('filter.from');
		$id .= ':' . $this->getState('filter.to');

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

		$query->select('s.submission_date, s.form_id, f.formname, s.price, s.currency, s.submit_key');
		$query->select('s.confirmed_date, s.confirmed_ip');
		$query->select('s.integration');
		$query->select('f.formname');
		$query->select('p.status, p.paid');
		$query->from('#__rwf_submitters AS s');
		$query->join('INNER', '#__rwf_forms AS f ON s.form_id = f.id');
		$query->join('LEFT', '(' . $subPayment . ') AS latest_payment ON latest_payment.submit_key = s.submit_key');
		$query->join('LEFT', '#__rwf_payment AS p ON p.id = latest_payment.id');
		$query->where("s.form_id = " . $form_id);

		if ($form_id)
		{
			$query->select('a.*');
			$query->join('INNER', '#__rwf_forms_' . $form_id . ' AS a ON s.answer_id = a.id');
		}

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

		$confirmed = $this->getState('filter.confirmed');

		if (is_numeric($confirmed))
		{
			if ($confirmed)
			{
				$query->where('s.confirmed_date > 0');
			}
			else
			{
				$query->where('s.confirmed_date = 0');
			}
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 's.submission_date');
		$orderDirn = $this->state->get('list.direction', 'desc');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		// To make sure submitter id will indeed be the 'id'
		$query->select('s.id');

		return $query;
	}

	/**
	 * Get a default form id
	 *
	 * @return int
	 */
	protected function getDefaultFormId()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from('#__rwf_forms');
		$query->order('id');

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res ? $res : 0;
	}

	/**
	 * Get form details
	 *
	 * @param   int  $id  form id
	 *
	 * @return bool|mixed
	 */
	public function getFormInfo($id = null)
	{
		if ($id == null)
		{
			$id = $this->getState('filter.form_id');
		}

		if ($id)
		{
			$query = $this->_db->getQuery(true);

			$query->select('id, formname, activatepayment, currency, enable_confirmation')
				->from('#__rwf_forms')
				->where('id = ' . $this->_db->Quote($id));

			$this->_db->setQuery($query);

			return $this->_db->loadObject();
		}
		else
		{
			return false;
		}
	}

	/**
	 * Return form fields
	 *
	 * @return mixed
	 */
	public function getFields()
	{
		$db = JFactory::getDBO();
		$form_id = $this->getState('filter.form_id');

		$query = ' SELECT f.id AS field_id, f.field '
			. '      , CASE WHEN (CHAR_LENGTH(f.field_header) > 0) THEN f.field_header ELSE f.field END AS field_header '
			. ' FROM #__rwf_fields AS f '
			. ' INNER JOIN #__rwf_form_field AS ff ON ff.field_id = f.id '
			. ' WHERE f.fieldtype <> "info" ';

		if ($form_id)
		{
			$query .= ' AND ff.form_id = ' . $db->Quote($form_id);
		}

		$query .= "GROUP BY f.id
				ORDER BY ff.ordering ";

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Delete items
	 *
	 * @param   mixed  $pks    id or array of ids of items to be deleted
	 * @param   bool   $force  force delete (in case of integration)
	 *
	 * @return  boolean
	 */
	public function delete($pks = null, $force = false)
	{
		// Initialise variables.
		$table = $this->getTable();
		$table->delete($pks, $force);

		return true;
	}
}
