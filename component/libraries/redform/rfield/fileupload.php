<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Rfield
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * redFORM field
 *
 * @package     Redform.Libraries
 * @subpackage  Rfield
 * @since       2.5
 */
class RdfRfieldFileupload extends RdfRfield
{
	protected $type = 'fileupload';

	/**
	 * Set field value from post data
	 *
	 * @param   string  $value  value
	 *
	 * @return string new value
	 */
	public function setValueFromPost($value)
	{
		/* Check if the folder exists */
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		$params = JComponentHelper::getParams('com_redform');

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('f.formname');
		$query->from('#__rwf_forms AS f');
		$query->where('f.id = ' . $db->Quote($this->load()->form_id));
		$db->setQuery($query);
		$formname = $db->loadResult();

		$filepath = JPATH_SITE . '/' . $params->get('upload_path', 'images/redform');
		$folder = JFile::makeSafe(str_replace(' ', '', $formname));

		$fullpath = $filepath . '/' . $folder;

		if (!JFolder::exists($fullpath))
		{
			if (!JFolder::create($fullpath))
			{
				JError::raiseWarning(0, JText::_('COM_REDFORM_CANNOT_CREATE_FOLDER') . ': ' . $fullpath);

				return false;
			}
		}

		clearstatcache();

		$src_file = $value['tmp_name'];

		// Make sure we have a unique name for file
		$dest_filename = uniqid() . '_' . basename($value['name']);

		if (JFolder::exists($fullpath))
		{
			/* Start processing uploaded file */
			if (is_uploaded_file($src_file))
			{
				if (JFolder::exists($fullpath) && is_writable($fullpath))
				{
					if (move_uploaded_file($src_file, $fullpath . '/' . $dest_filename))
					{
						$this->value = $fullpath . '/' . $dest_filename;
					}
					else
					{
						JError::raiseWarning(0, JText::_('COM_REDFORM_CANNOT_UPLOAD_FILE'));

						return false;
					}
				}
				else
				{
					JError::raiseWarning(0, JText::_('COM_REDFORM_FOLDER_DOES_NOT_EXIST'));

					return false;
				}
			}
		}
		else
		{
			JError::raiseWarning(0, JText::_('COM_REDFORM_FOLDER_DOES_NOT_EXIST'));

			return false;
		}

		return $this->value;
	}

	/**
	 * Returns field Input
	 *
	 * @return string
	 */
	public function getInput()
	{
		$properties = $this->getInputProperties();

		if ($this->getValue())
		{
			// Not re-uploading on edit form
			return '';
		}

		return parent::getInput();

		return sprintf('<input %s/>', $this->propertiesToString($properties));
	}

	/**
	 * Return input properties array
	 *
	 * @return array
	 */
	public function getInputProperties()
	{
		$properties = array();
		$properties['type'] = 'file';

		$properties['name'] = $this->getFormElementName();
		$properties['id'] = $this->getFormElementId();

		$properties['class'] = 'fileupload' . trim($this->getParam('class'));

		if ($this->load()->validate)
		{
			$properties['class'] = ' required';
		}

		if ($placeholder = $this->getParam('placeholder'))
		{
			$properties['placeholder'] = addslashes($placeholder);
		}

		return $properties;
	}
}
