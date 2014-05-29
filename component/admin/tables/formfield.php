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
 * form field relation
 *
 * @package     Redform.Backend
 * @subpackage  Tables
 * @since       3.0
 */
class RedformTableFormField extends RTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'rwf_form_field';

	/**
	 * @var int Primary key
	 */
	public $id = null;

	/**
	 * @var int
	 */
	public $field_id = null;

	/**
	 * @var int
	 */
	public $form_id = null;

	/**
	 * @var int
	 */
	public $validate = null;

	/**
	 * @var int
	 */
	public $published = null;

	/**
	 * @var int
	 */
	public $unique = null;

	/**
	 * @var int
	 */
	public $readonly = null;

	/**
	 * @var int
	 */
	public $ordering = null;

	protected $_tableFieldState = 'published';

	/**
	 * Called before store().
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	public function beforeStore($updateNulls = false)
	{
		if (!$this->ordering)
		{
			$this->ordering = $this->getNextOrder('form_id = ' . (int) $this->form_id);
		}

		return parent::beforeStore($updateNulls);
	}

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
		$q = "ALTER TABLE " . $db->quoteName('#__rwf_forms_' . $this->form_id) . " DROP " . $db->quoteName('field_' . $this->field_id);
		$db->setQuery($q);

		if (!$db->execute())
		{
			throw new Exception(JText::_('COM_REDFORM_Cannot_remove_field_from_form_table') . ' ' . $db->getError());
		}

		$this->reorder('form_id = ' . $this->form_id);

		return true;
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
		if (!$this->updateFieldTable())
		{
			return false;
		}

		return parent::afterStore($updateNulls);
	}

	/**
	 * update corresponding table column if needed
	 *
	 * @return boolean true on success
	 */
	private function updateFieldTable()
	{
		$db = JFactory::getDBO();

		/* column name for this field */
		$dbfieldname = 'field_' . $this->field_id;

		/* Get columns from the active form */
		$q = "SHOW COLUMNS FROM " . $db->quoteName($db->getPrefix() . 'rwf_forms_' . $this->form_id)
			. " WHERE  " . $db->quoteName('Field') . " = " . $db->Quote($dbfieldname);
		$db->setQuery($q);
		$result = $db->loadResult();

		/* Check if the field already exists */
		if (!$result)
		{
			/* Field doesn't exist, need to create it */
			$q = ' ALTER TABLE ' . $db->quoteName('#__rwf_forms_' . $this->form_id)
				. ' ADD ' . $db->quoteName($dbfieldname) . ' TEXT NULL';
			$db->setQuery($q);

			if (!$db->execute())
			{
				$this->setError($db->getErrorMsg());

				return false;
			}
		}

		/* Get indexes from the active form */
		$indexresult = null;
		$q = "SHOW KEYS FROM " . $db->quoteName($db->getPrefix() . 'rwf_forms_' . $this->form_id)
			. " WHERE key_name = " . $db->Quote($dbfieldname);
		$db->setQuery($q);
		$db->execute();
		$indexresult = $db->loadAssocList('Key_name');

		/* Check if the field has to be unique */
		$q = "ALTER TABLE " . $db->quoteName('#__rwf_forms_' . $this->form_id);

		if ($this->unique && !isset($indexresult[$dbfieldname]))
		{
			$q .= ' ADD UNIQUE (' . $db->quoteName($dbfieldname) . ' (255))';
			$db->setQuery($q);

			if (!$db->execute())
			{
				$this->setError(JText::_('COM_REDFORM_Cannot_make_the_field_unique') . ' ' . $db->getErrorMsg());

				return false;
			}
		}
		elseif (isset($indexresult[$dbfieldname]))
		{
			$q .= ' DROP INDEX' . $db->quoteName($dbfieldname);
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
