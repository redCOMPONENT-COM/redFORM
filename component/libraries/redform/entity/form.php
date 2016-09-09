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
	 * @var string
	 */
	private $statusMessage;

	/**
	 * @var array
	 */
	private $renderOptions;

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

		if (strtotime($this->startdate) > time())
		{
			$this->statusMessage = JText::_('COM_REDFORM_STATUS_NOT_STARTED');

			return false;
		}

		if ($this->formexpires && strtotime($this->enddate) < time())
		{
			$this->statusMessage = JText::_('COM_REDFORM_STATUS_EXPIRED');

			return false;
		}

		if ($this->access > 1 && !$user->get('id'))
		{
			$this->statusMessage = JText::_('COM_REDFORM_STATUS_REGISTERED_ONLY');

			return false;
		}

		if (!in_array($this->access, $user->getAuthorisedViewLevels()))
		{
			$this->statusMessage = JText::_('COM_REDFORM_STATUS_SPECIAL_ONLY');

			return false;
		}

		return true;
	}

	/**
	 * Get form fields
	 *
	 * @return RdfRfieldFactory[]
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
	 * Check if form has fields in multiple sections
	 * 
	 * @return bool
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
