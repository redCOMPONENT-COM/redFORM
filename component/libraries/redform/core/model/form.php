<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Core.Model
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Class RdfCoreModelSubmission
 *
 * @package     Redform.Libraries
 * @subpackage  Core.Model
 * @since 3.0
 */
class RdfCoreModelForm extends RModel
{
	/**
	 * item id
	 * @var int
	 */
	protected $id;

	/**
	 * Caching
	 * @var object
	 */
	protected $form;

	/**
	 * Constructor
	 *
	 * @param   int  $form_id  form id
	 */
	public function __construct($form_id = null)
	{
		parent::__construct();

		if ($form_id)
		{
			$this->setId($form_id);
		}
	}

	/**
	 * Method to set the form identifier
	 *
	 * @param   int  $id  event identifier
	 *
	 * @return void
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	public function getForm()
	{
		if (!$this->form)
		{
			$table = RTable::getInstance('Form', 'RedformTable');
			$table->load($this->id);

			$this->form = $table;
		}

		return $this->form;
	}

	/**
	 * get the form fields
	 *
	 * @return array RdfRfield
	 */
	public function getFormFields()
	{
		$form_id = $this->id;

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from('#__rwf_fields');
		$query->where('form_id = ' . $form_id);
		$query->order('ordering');

		$db->setQuery($query);
		$ids = $db->loadColumn();

		$fields = array();

		foreach ($ids as $fieldId)
		{
			$fields[] = RdfRfieldFactory::getField($fieldId);
		}

		return $fields;
	}
}
