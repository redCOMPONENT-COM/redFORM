<?php
/**
 * @package     redform.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Currency table.
 *
 * @package     Redshopb.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedformTableField extends RTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'rwf_fields';

	/**
	 * @var int Primary key
	 */
	public $id = null;

	/**
	 * @var string field name
	 */
	public $field = null;

	/**
	 * @var string field header for tables
	 */
	public $field_header = null;

	/**
	 * @var string field type
	 */
	public $fieldtype = 'textfield';


	/**
	 * @var int published state
	 */
	public $published = null;


	/**
	 * @var int id of user having checked out the item
	 */
	public $checked_out = null;


	/**
	 * @var string
	 */
	public $checked_out_time = null;


	/**
	 * @var int
	 */
	public $form_id = null;

	/**
	 * @var int
	 */
	public $ordering = null;

	/**
	 * @var int
	 */
	public $validate = null;

	/**
	 * @var int
	 */
	public $unique = null;

	/**
	 * @var int
	 */
	public $readonly = 0;

	/**
	 * @var string The default value for a field
	 */
	public $default = null;

	/**
	 * @var string The tooltip for a field
	 */
	public $tooltip = null;

	/**
	 * @var string linked redmember field db name
	 */
	public $redmember_field = null;

	/**
	 * @var string custom params
	 */
	public $params = null;

	/**
	 * Current row state before updating/saving
	 *
	 * @var null
	 */
	private $beforeupdate = null;

	/**
	 * Field name to publish/unpublish/trash table registers. Ex: state
	 *
	 * @var  string
	 */
	protected $_tableFieldState = 'published';

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws Exception
	 */
	public function delete($pk = null)
	{
		// Load first
		$this->load($pk);

		if (!parent::delete($pk))
		{
			return false;
		}

		$db = JFactory::getDbo();

		// Delete associated field in form table
		$q = "ALTER TABLE " . $db->quoteName('#__rwf_forms_' . $this->form_id) . " DROP " . $db->quoteName($this->field);
		$db->setQuery($q);

		if (!$db->query())
		{
			throw new Exception(JText::_('COM_REDFORM_Cannot_remove_field_from_form_table') . ' ' . $db->getError());
		}

		// Delete associated values
		$query = $db->getQuery(true);

		$query->delete();
		$query->from('#__rwf_values');
		$query->where('field_id = ' . $pk);

		if (!$db->query())
		{
			throw new Exception(JText::_('COM_REDFORM_A_problem_occured_when_deleting_the_field_values') . ' ' . $db->getError());
		}

		return true;
	}

	/**
	 * Called before store().
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	protected function beforeStore($updateNulls = false)
	{
		if ($this->id)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('*');
			$query->from('#__rwf_fields');
			$query->where('id = ' . $this->id);

			$db->setQuery($query);
			$this->beforeupdate = $db->loadObject();
		}

		return parent::beforeStore($updateNulls);
	}

	/**
	 * Called after store().
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	protected function afterStore($updateNulls = false)
	{
		if (!$this->updateFieldTable($this, $this->beforeupdate))
		{
			return false;
		}

		return parent::beforeStore($updateNulls);
	}

	/**
	 * update corresponding table column if needed
	 *
	 * @param   object  $row     field table record object with updated value
	 * @param   object  $oldrow  previously recorded field table record object corresponding to current field id
	 *
	 * @return boolean true on success
	 */
	private function updateFieldTable($row, $oldrow)
	{

		$db = JFactory::getDBO();

		/* column name for this field */
		$field = 'field_' . $row->id;

		/* Get columns from the active form */
		$q = "SHOW COLUMNS FROM " . $db->quoteName($db->getPrefix() . 'rwf_forms_' . $row->form_id)
			. " WHERE  " . $db->quoteName('Field') . " = " . $db->Quote($field);
		$db->setQuery($q);
		$result = $db->loadResult();

		/* Check if the field already exists */
		if (!$result)
		{
			/* Field doesn't exist, need to create it */
			$q = ' ALTER TABLE ' . $db->quoteName('#__rwf_forms_' . $row->form_id)
				. ' ADD ' . $db->quoteName($field) . ' TEXT NULL';
			$db->setQuery($q);

			if (!$db->execute())
			{
				$this->setError($db->getErrorMsg());

				return false;
			}
		}

		/* Check if the field moved form */
		if ($oldrow->form_id && $row->form_id <> $oldrow->form_id)
		{
			$result = array();
			/* Check if the column exists on the old table */
			$q = "SHOW COLUMNS FROM " . $db->quoteName($db->getPrefix() . 'rwf_forms_' . $oldrow->form_id)
				. " WHERE  " . $db->quoteName('Field') . " = " . $db->Quote($field);
			$db->setQuery($q);
			$db->execute();
			$result = $db->loadResult();

			/* Check if the field already exists */
			if ($result)
			{
				/* Drop the old column */
				$q = "ALTER TABLE " . $db->quoteName('#__rwf_forms_' . $oldrow->form_id)
					. " DROP " . $db->quoteName($field);
				$db->setQuery($q);

				if (!$db->execute())
				{
					$this->setError(JText::_('COM_REDFORM_Cannot_remove_field_from_old_form') . ' ' . $db->getErrorMsg());

					return false;
				}
			}
		}

		/* Get indexes from the active form */
		$indexresult = null;
		$q = "SHOW KEYS FROM " . $db->quoteName($db->getPrefix() . 'rwf_forms_' . $row->form_id)
			. " WHERE key_name = " . $db->Quote($field);
		$db->setQuery($q);
		$db->execute();
		$indexresult = $db->loadAssocList('Key_name');

		/* Check if the field has to be unique */
		$q = "ALTER TABLE " . $db->quoteName('#__rwf_forms_' . $row->form_id);

		if ($row->unique && !isset($indexresult[$field]))
		{
			$q .= ' ADD UNIQUE (' . $db->quoteName($field) . ' (255))';
			$db->setQuery($q);

			if (!$db->execute())
			{
				$this->setError(JText::_('COM_REDFORM_Cannot_make_the_field_unique') . ' ' . $db->getErrorMsg());

				/* Remove unique status */
				$q = "UPDATE " . $db->quoteName('#__rwf_fields') . "
					SET " . $db->quoteName('unique') . " = 0
					WHERE id = " . $row->id;
				$db->setQuery($q);
				$db->execute();
			}
		}
		elseif (isset($indexresult[$field]))
		{
			$q .= ' DROP INDEX' . $db->quoteName($field);
			$db->setQuery($q);

			if (!$db->execute())
			{
				$this->setError(JText::_('COM_REDFORM_Cannot_remove_the_field_unique_status') . ' ' . $db->getErrorMsg());

				return false;
			}
		}

		return true;
	}
}
