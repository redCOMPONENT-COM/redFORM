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
 * Fields table.
 *
 * @package     Redform.Backend
 * @subpackage  Tables
 * @since       1.5
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
		// Check if associated to forms
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id')->from('#__rwf_form_field')->where('field_id = ' . $this->id);

		$db->setQuery($query);
		$res = $db->loadResult();

		if ($res)
		{
			$this->setError('COM_REDFORM_FIELD_DELETE_ERROR_USED_IN_FORMS');

			return false;
		}

		if (!parent::delete($pk))
		{
			return false;
		}

		// Delete associated values
		$query = $db->getQuery(true);

		$query->delete();
		$query->from('#__rwf_values');
		$query->where('field_id = ' . $pk);
		$db->setQuery($query);

		if (!$db->execute())
		{
			throw new Exception(JText::_('COM_REDFORM_A_problem_occured_when_deleting_the_field_values') . ' ' . $db->getError());
		}

		return true;
	}
}
