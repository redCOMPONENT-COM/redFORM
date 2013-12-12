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
 * Company Model
 *
 * @package     Redform.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedformModelForm extends RModelAdmin
{
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

		$this->AddFormTable($this->getState($this->getName() . '.id'));

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   11.1
	 */
	protected function canDelete($record)
	{
		if (!parent::canDelete($record))
		{
			return false;
		}

		// Check that there are no submitters
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from('#__rwf_submitters');
		$query->where('form_id = ' . $record->id);

		$db->setQuery($query);
		$res = $db->loadResult();

		if ($res)
		{
			$this->setError('COM_REDFORM_FORM_DELETE_ERROR_FORM_HAS_SUBMITTERS');

			return false;
		}

		return true;
	}

	/**
	 * Adds a table if it doesn't exist yet
	 *
	 * @param   int  $formid  form id
	 *
	 * @return boolean true on success
	 *
	 * @throws Exception
	 */
	private function AddFormTable($formid)
	{
		$db = JFactory::getDBO();

		/* construct form name */
		$q = "SHOW TABLES LIKE " . $db->Quote($db->getPrefix() . 'rwf_forms_' . $formid);
		$db->setQuery($q);
		$result = $db->loadColumn();

		if (count($result) == 0)
		{
			/* Table doesn't exist, need to create it */
			$q = "CREATE TABLE " . $db->quoteName('#__rwf_forms_' . $formid) . " (";
			$q .= $db->quoteName('id') . " INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ";
			$q .= ") COMMENT = " . $db->Quote('redFORMS Form ' . $formid);
			$db->setQuery($q);

			if (!$db->query())
			{
				throw new Exception($db->getError());
			}
		}

		return true;
	}

	/**
	 * Clones the forms and their fields
	 *
	 * @param   array  $cids  id(s) of form(s) to clone
	 *
	 * @return bool
	 */
	function copy($cids = array())
	{
		foreach ($cids as $cid)
		{
			// Get the form
			$form = $this->getTable('redform', 'RedformTable');
			$form->load($cid);

			// Get associated fields
			$fields = $form->getFormFields();

			// Copy the form
			$form->id = null;
			$form->formname = JText::_('COM_REDFORM_Copy_of') . ' ' . $form->formname;
			$form->store();

			/* Add form table */
			$this->AddFormTable($form->id);

			// Now copy the fields
			foreach ($fields as $field_id)
			{
				// Fetch field
				$field = $this->getTable('fields', 'RedformTable');
				$field->load($field_id);

				// Get associated values
				$values = $field->getValues();

				// Copy the form
				$field->id = null;
				$field->form_id = $form->id;

				$fieldmodel = JModel::getInstance('field', 'RedformModel');
				$newfield = $fieldmodel->store($field->getProperties());

				// Copy associated values
				foreach ($values as $v)
				{
					// Get value
					$value = $this->getTable('values', 'RedformTable');
					$value->load($v);

					$value->id = null;
					$value->field_id = $newfield->id;
					$valuemodel = JModel::getInstance('value', 'RedformModel');
					$data = $value->getProperties();
					$data['form_id'] = $form->id;
					unset($data['ordering']);
					$valuemodel->store($data);
				}
			}
		}

		return true;
	}

	/**
	 * Returns form fields as options
	 *
	 * @param   null  $id  form id
	 *
	 * @return array
	 */
	public function getFieldsOptions($id = null)
	{
		if (!($id) && $this->_id)
		{
			$id = $this->_id;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('f.id AS value, f.field AS text');
		$query->from('#__rwf_fields AS f');
		$query->where('f.form_id = ' . (int) $id);
		$query->order('f.field');

		$db->setQuery($query);
		$res = $db->loadObjectList();

		return $res;
	}
}
