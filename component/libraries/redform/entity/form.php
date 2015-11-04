<?php
/**
 * @package     Redform.Library
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Submitter entity.
 *
 * @since  3.0
 */
class RdfEntityForm extends RdfEntityBase
{
	/**
	 * Form fields
	 *
	 * @var array
	 */
	protected $formFields;

	/**
	 * Get form fields
	 *
	 * @return array
	 */
	public function getFormFields()
	{
		if (empty($this->formFields))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('ff.id');
			$query->from('#__rwf_form_field AS ff');
			$query->where('ff.form_id = ' . $this->id);
			$query->order('ff.ordering');

			$db->setQuery($query);
			$ids = $db->loadColumn();

			$fields = array();

			foreach ($ids as $formfieldId)
			{
				$fields[] = RdfRfieldFactory::getFormField($formfieldId);
			}

			$this->formFields = $fields;
		}

		return $this->formFields;
	}
}
