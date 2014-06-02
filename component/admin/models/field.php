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
 * Field Model
 *
 * @package     Redform.Backend
 * @subpackage  Models
 * @since       2.5
 */
class RedformModelField extends RModelAdmin
{
	/**
	 * Override to add hasOptions status
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if ($item)
		{
			$item->hasOptions = RdfRfieldFactory::getFieldType($item->fieldtype)->hasOptions;
		}

		return $item;
	}

	/**
	 * Override to replace the global params field with specific field params
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		parent::preprocessForm($form, $data, $group);

		if (is_object($data))
		{
			$data = get_object_vars($data);
		}

		if (isset($data['fieldtype']))
		{
			$form->removeField('params');
			$xml = RdfRfieldFactory::getFieldType($data['fieldtype'])->getXmlPath();
			$form->loadFile($xml, false);
		}
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 */
	public function save($data)
	{
		if (!parent::save($data))
		{
			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * returns redmember fields as options
	 *
	 * @return array
	 */
	public function getRedmemberFieldsOptions()
	{
		$query = ' SELECT f.field_dbname AS value, CASE WHEN (t.tab_name) THEN CONCAT(t.tab_name, " - ", f.field_name) ELSE f.field_name END AS text '
		       . ' FROM #__redmember_fields AS f '
		       . ' LEFT JOIN #__redmember_tab AS t ON t.tab_id = f.field_tabid '
		       . ' ORDER BY t.tab_name, f.field_name '
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		return $res;
	}

	/**
	 * Get Associated values (options)
	 *
	 * @return mixed
	 */
	public function getValues()
	{

		$field = $this->getItem();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('v.*');
		$query->from('#__rwf_values AS v');
		$query->where('v.field_id = ' . $db->Quote($field->id));
		$query->order('v.ordering');

		$db->setQuery($query);
		$res = $db->loadObjectList();

		return $res;
	}

	/**
	 * Save value
	 *
	 * @param   array  $data  value array data to save (id value, label, price)
	 *
	 * @return int row id on success
	 */
	public function saveValue($data)
	{
		$row = $this->getTable('Values', 'RedformTable');
		$row->bind($data);

		if (!$data['id'])
		{
			$row->published = 1;

			if ($current = $this->getValues())
			{
				$maxordering = array_pop($current)->ordering;
				$row->ordering = $maxordering + 1;
			}
		}

		if (!($row->check() && $row->store()))
		{
			$this->setError($row->getError());

			return false;
		}

		return $row->id;
	}

	/**
	 * Remove value
	 *
	 * @param   int  $id  value id
	 *
	 * @return bool
	 */
	public function removeValue($id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->delete('#__rwf_values');
		$query->where('id = ' . $id);

		$db->setQuery($query);

		if (!$res = $db->execute())
		{
			$this->setError($db->getError());

			return false;
		}

		return $res;
	}

	/**
	 * copy fields
	 *
	 * @param   array  $field_ids  field ids
	 *
	 * @return boolean true on success
	 */
	public function copy($field_ids)
	{
		foreach ($field_ids as $field_id)
		{
			$row = $this->getTable('Field', 'RedformTable');
			$row->load($field_id);
			$row->id = null;
			$row->field = Jtext::_('COM_REDFORM_COPY_OF') . ' ' . $row->field;

			/* pre-save checks */
			if (!$row->check())
			{
				$this->setError(JText::_('COM_REDFORM_There_was_a_problem_checking_the_field_data'), 'error');

				return false;
			}

			/* save the changes */
			if (!$row->store())
			{
				$this->setError(JText::_('COM_REDFORM_There_was_a_problem_storing_the_field_data'), 'error');

				return false;
			}

			// Copy associated values
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('*')->from('#__rwf_values')->where('field_id = ' . $field_id);

			$db->setQuery($query);
			$res = $db->loadObjectList();

			foreach ($res as $r)
			{
				/* Load the table */
				$valuerow = $this->getTable('Value', 'RedformTable');
				$valuerow->bind(get_object_vars($r));
				$valuerow->id = null;
				$valuerow->field_id = $row->id;

				/* save the changes */
				if (!$valuerow->store())
				{
					$this->setError(JText::_('COM_REDFORM_There_was_a_problem_copying_field_options') . ' ' . $row->getError(), 'error');

					return false;
				}
			}
		}

		return true;
	}
}
