<?php
/**
 * @package     Redform.Frontend
 * @subpackage  mod_orders_stats
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Orders model
 *
 * @since  1.0
 */
class ModordersstatsLibModelOrders extends RModel
{
	private $params;

	/**
	 * Constructor
	 *
	 * @param   array  $params  params
	 */
	public function __construct($params)
	{
		parent::__construct();

		$this->params = $params;
	}

	/**
	 * Get orders
	 *
	 * @param   int  $formId  form id
	 *
	 * @return array
	 */
	public function getOrders($formId)
	{
		$orders = array();

		$submissions = $this->getSubmissions($formId);

		foreach ($submissions as $submission)
		{
			$orders[] = $this->mapSubmission($submission);
		}

		return $orders;
	}

	/**
	 * Get submissions from db
	 *
	 * @param   int  $formId  form id
	 *
	 * @return mixed
	 */
	private function getSubmissions($formId)
	{
		$model = RModel::getAdminInstance('Submitters', array('ignore_request' => true), 'com_redform');
		$model->setState('filter.form_id', $formId);
		$model->setState('filter.from', date('Y-m-d 00:00:00', time()));
		$model->setState('filter.to', date('Y-m-d 23:59:59', time()));
		$model->setState('limit', 0);

		return $model->getItems();
	}

	/**
	 * Map submission to order
	 *
	 * @param   object  $submission  data
	 *
	 * @return ModordersstatsLibOrder
	 */
	private function mapSubmission($submission)
	{
		$order = new ModordersstatsLibOrder;

		$order->date = $submission->submission_date;

		if ($name = $this->mapField('nameFields', $submission))
		{
			$order->salesPerson = $name;
		}

		if ($company = $this->mapField('companyFields', $submission))
		{
			$order->company = $company;
		}
		elseif ($company = $this->getCompany($order->salesPerson))
		{
			$order->company = $company;
		}

		if ($val = $this->mapField('elFields', $submission))
		{
			// Checkbox or radio depending on form
			if ($val == 1 || $val == 'new')
			{
				$order->hasElec = 1;
			}
		}

		if ($val = $this->mapField('gasFields', $submission))
		{
			// Checkbox or radio depending on form
			if ($val == 1 || $val == 'new')
			{
				$order->hasGas = 1;
			}
		}

		return $order;
	}

	/**
	 * Map form fields to order
	 *
	 * @param   string  $name        field name
	 * @param   object  $submission  submission data
	 *
	 * @return string
	 */
	private function mapField($name, $submission)
	{
		$fieldIds = $this->params->get($name);

		if (!(is_array($fieldIds) && count($fieldIds)))
		{
			return false;
		}

		foreach ($fieldIds as $fieldId)
		{
			$field = 'field_' . $fieldId;

			if (isset($submission->$field))
			{
				return $submission->$field;
			}
		}

		return false;
	}

	/**
	 * Get company name from user group name
	 *
	 * @param   string  $userFullname  user name
	 *
	 * @return mixed
	 */
	private function getCompany($userFullname)
	{
		if ($id = $this->getUserId($userFullname))
		{
			$groupsIds = JUserHelper::getUserGroups($id);
			$companyGroups = $this->params->get('companyGroups');
			$found = array_intersect($groupsIds, $companyGroups);

			if ($found && count($found))
			{
				return $this->getGroupName($found[0]);
			}
		}
	}


	/**
	 * Returns userid if a user exists
	 *
	 * @param   string  $userFullname  The name to search on.
	 *
	 * @return  integer  The user id or 0 if not found.
	 */
	private function getUserId($userFullname)
	{
		$db = $this->_db;
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__users'));
		$query->where($db->quoteName('name') . ' = ' . $db->quote($userFullname));
		$db->setQuery($query, 0, 1);

		return $db->loadResult();
	}

	/**
	 * return usergroup name
	 *
	 * @param   int  $id  id
	 *
	 * @return mixed
	 */
	private function getGroupName($id)
	{
		$db = $this->_db;
		$query = $db->getQuery(true);
		$query->select($db->quoteName('title'));
		$query->from($db->quoteName('#__usergroups'));
		$query->where($db->quoteName('id') . ' = ' . $db->quote($id));
		$db->setQuery($query, 0, 1);

		return $db->loadResult();
	}
}