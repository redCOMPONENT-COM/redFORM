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
	 * form fields
	 *
	 * @var array
	 */
	protected $fields;

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
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();

		// Get a storage key.
		$store = $this->getStoreId();

		$items = $this->addPaymentinfo($items);

		// Add the items to the internal cache.
		$this->cache[$store] = $items;

		return $items;
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

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('s.submission_date, s.form_id, f.formname, s.price, s.vat, s.currency, s.submit_key');
		$query->select('s.confirmed_date, s.confirmed_ip, s.confirmed_type');
		$query->select('s.integration');
		$query->select('f.formname');
		$query->from('#__rwf_submitters AS s');
		$query->join('INNER', '#__rwf_forms AS f ON s.form_id = f.id');
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

		// Filter search
		$search = $this->getState('filter.search_submitters');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');

			$lookup = array();
			$lookup[] = 's.submit_key LIKE ' . $search;

			if ($fields = $this->getFields())
			{
				foreach ($this->getFields() as $field)
				{
					$lookup[] = 'a.field_' . $field->field_id . ' LIKE ' . $search;
				}
			}

			$query->where('(' . implode(' OR ', $lookup) . ')');
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
		if (!$this->fields)
		{
			$form_id = $this->getState('filter.form_id');
			$query = $this->_db->getQuery(true);

			$query->select('f.id AS field_id, f.field')
				->select('CASE WHEN (CHAR_LENGTH(f.field_header) > 0) THEN f.field_header ELSE f.field END AS field_header')
				->from('#__rwf_fields AS f')
				->join('INNER', '#__rwf_form_field AS ff ON ff.field_id = f.id')
				->where('f.fieldtype NOT IN ("info", "submissionprice")')
				->group('f.id')
				->order('ff.ordering');

			if ($form_id)
			{
				$query->where('ff.form_id = ' . $this->_db->Quote($form_id));
			}

			$this->_db->setQuery($query);
			$this->fields = $this->_db->loadObjectList();
		}

		return $this->fields;
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

	/**
	 * Method to add number of submitters to forms
	 *
	 * @param   array  $items  items
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	private function addPaymentinfo($items)
	{
		if (!$items)
		{
			return $items;
		}

		$keys = array();

		foreach ($items as $i)
		{
			$keys[] = $i->id;
		}

		$keys = array_map(array($this->_db, 'quote'), $keys);

		$query = $this->_db->getQuery(true);

		$query->select('pr.id AS prid, pr.submission_id, pr.price, pr.vat, pr.vat, pr.created, pr.currency')
			->select('p.id AS payment_id, p.paid, p.status, p.date')
			->from('#__rwf_payment_request AS pr')
			->join('LEFT', '#__rwf_cart_item AS ci ON ci.payment_request_id = pr.id')
			->join('LEFT', '#__rwf_payment AS p ON p.cart_id = ci.cart_id')
			->where('pr.submission_id IN (' . implode(', ', array_unique($keys)) . ')')
			->order('p.id DESC, pr.id DESC');

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		$paymentrequests = $this->buildPaymentrequests($res);
		$paymentrequests = $this->addPaymentrequestsPayments($paymentrequests, $res);

		foreach ($items as &$i)
		{
			if (isset($paymentrequests[$i->id]))
			{
				$i->paymentrequests = $paymentrequests[$i->id];
			}
			else
			{
				$i->paymentrequests = false;
			}
		}

		return $items;
	}

	/**
	 * Build payment requests array from table
	 *
	 * @param   array  $results  payment request + payments
	 *
	 * @return array
	 */
	private function buildPaymentrequests($results)
	{
		$requests = array();

		foreach ($results as $result)
		{
			if (!isset($requests[$result->submission_id]))
			{
				$requests[$result->submission_id] = array();
			}
			elseif (isset($requests[$result->submission_id][$result->prid]))
			{
				continue;
			}

			$paymentrequest = clone $result;

			unset($paymentrequest->payment_id);
			unset($paymentrequest->status);
			unset($paymentrequest->date);

			$paymentrequest->paid = 0;
			$paymentrequest->status = '';
			$paymentrequest->payments = array();

			$requests[$paymentrequest->submission_id][$paymentrequest->prid] = $paymentrequest;
		}

		return $requests;
	}

	/**
	 * Add payment to payment requests
	 *
	 * @param   array  $paymentrequests  payment requests
	 * @param   array  $results          results from query
	 *
	 * @return mixed
	 */
	private function addPaymentrequestsPayments($paymentrequests, $results)
	{
		foreach ($results as $result)
		{
			if (!$result->payment_id)
			{
				continue;
			}

			$payment = new stdClass;
			$payment->id = $result->payment_id;
			$payment->paid = $result->paid;
			$payment->status = $result->status;
			$payment->date = $result->date;

			$paymentrequests[$result->submission_id][$result->prid]->paid = $payment->paid ? 1 : 0;
			$paymentrequests[$result->submission_id][$result->prid]->status = $payment->status ? $payment->status : '';
			$paymentrequests[$result->submission_id][$result->prid]->payments[] = $payment;
		}

		return $paymentrequests;
	}
}
