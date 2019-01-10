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
	use \Redform\Entity\Traits\HasParams;

	/**
	 * Form fields
	 *
	 * @var RdfRfield[]
	 */
	protected $formFields;

	/**
	 * @var string
	 */
	private $statusMessage;

	/**
	 * @var array
	 */
	private $renderOptions;

	/**
	 * Proxy item properties
	 *
	 * @param   string  $property  Property tried to access
	 *
	 * @return  mixed   $this->item->property if it exists
	 */
	public function __get($property)
	{
		if (null != $this->item && $property == 'params')
		{
			return new JRegistry($this->item->params);
		}

		return parent::__get($property);
	}

	/**
	 * return form status
	 *
	 * @param   JUser  $user  user
	 *
	 * @return boolean
	 */
	public function checkFormStatus($user = null)
	{
		if (!$this->isValid())
		{
			throw new InvalidArgumentException('entity has no valid id');
		}

		$this->statusMessage = null;

		$user = $user instanceof JUSer ? $user : JFactory::getUser();

		if (!$this->published)
		{
			$this->statusMessage = JText::_('COM_REDFORM_STATUS_NOT_PUBLISHED');

			return false;
		}

		$now = JFactory::getDate();

		if (JFactory::getDate($this->startdate) > $now)
		{
			$this->statusMessage = JText::_('COM_REDFORM_STATUS_NOT_STARTED');

			return false;
		}

		if ($this->formexpires && JFactory::getDate($this->enddate) < $now)
		{
			$this->statusMessage = JText::_('COM_REDFORM_STATUS_EXPIRED');

			return false;
		}

		if (!in_array($this->access, $user->getAuthorisedViewLevels()))
		{
			$this->statusMessage = JText::_('LIB_REDFORM_FORM_ACCESS_CHECK_DENIED');

			return false;
		}

		return true;
	}

	/**
	 * Get a specific field of the form
	 *
	 * @param   integer  $id  field id
	 *
	 * @return RdfRfield or false if not found
	 *
	 * @since 3.3.23
	 */
	public function getField($id)
	{
		$fields = $this->getFormFields();

		foreach ($fields as $field)
		{
			if ($field->fieldId == $id)
			{
				return $field;
			}
		}

		return false;
	}

	/**
	 * Get a specific field of the form
	 *
	 * @param   integer  $formFieldId  field id
	 *
	 * @return RdfRfield or false if not found
	 *
	 * @since 3.3.23
	 */
	public function getFormField($formFieldId)
	{
		$fields = $this->getFormFields();

		foreach ($fields as $field)
		{
			if ($field->id == $formFieldId)
			{
				return $field;
			}
		}

		return false;
	}

	/**
	 * Get form fields
	 *
	 * @return RdfRfield[]
	 */
	public function getFormFields()
	{
		if (empty($this->formFields))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('ff.id');
			$query->from('#__rwf_form_field AS ff');
			$query->innerJoin('#__rwf_section AS s ON s.id = ff.section_id');
			$query->where('ff.form_id = ' . $this->id);
			$query->order('s.ordering, ff.ordering');

			$db->setQuery($query);
			$ids = $db->loadColumn();

			$fields = array();

			foreach ($ids as $formfieldId)
			{
				$fields[] = RdfRfieldFactory::getFormField($formfieldId, $this);
			}

			$this->formFields = $fields;
		}

		return $this->formFields;
	}

	/**
	 * Set options for rendering (passed from integration when calling RdfCore->getFormFields())
	 *
	 * @return RdfEntityForm
	 */
	public function getRenderOptions()
	{
		return $this->renderOptions;
	}

	/**
	 * Return status message after a checkFormStatus
	 *
	 * @return string
	 */
	public function getStatusMessage()
	{
		return $this->statusMessage;
	}

	/**
	 * Check if form has an email field with notification enabled
	 *
	 * @return boolean
	 */
	public function hasEmailContactField()
	{
		$fields = $this->getFormFields();

		foreach ($fields as $field)
		{
			if ($field->fieldtype == 'email' && $field->getParam('notify', 1))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if form has fields in multiple sections
	 *
	 * @return boolean
	 */
	public function hasMultipleSections()
	{
		$fields = $this->getFormFields();

		$sections = array();

		foreach ($fields as $field)
		{
			if (!in_array($field->section_id, $sections))
			{
				$sections[] = $field->section_id;

				if (count($sections) > 1)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Set options for rendering (passed from integration when calling RdfCore->getFormFields())
	 *
	 * @param   array  $options  options
	 *
	 * @return RdfEntityForm
	 */
	public function setRenderOptions($options)
	{
		$this->renderOptions = $options;

		return $this;
	}
}
