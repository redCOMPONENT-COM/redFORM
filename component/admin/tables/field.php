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
