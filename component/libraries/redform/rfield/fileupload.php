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
	 * Get and set the value from post data, using appropriate filtering
	 *
	 * @param   int  $signup  form instance number for the field
	 *
	 * @return mixed
	 */
	public function getValueFromPost($signup)
	{
		if ($value = $this->getFileUpload($signup))
		{
			return $value;
		}

		// No upload, look for a previous value
		$input = JFactory::getApplication()->input;
		$this->value = $input->getString($this->getPostName($signup) . '_prev', '');

		return $this->value;
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

	/**
	 * Check if there was a file uploaded
	 *
	 * @param   int  $signup  signup id
	 *
	 * @return bool|string
	 */
	private function getFileUpload($signup)
	{
		if (!$fullpath = $this->getStoragePath())
		{
			return false;
		}

		$input = JFactory::getApplication()->input;

		if (!$upload = $input->files->get($this->getPostName($signup), array(), 'array'))
		{
			return false;
		}

		$src_file = $upload['tmp_name'];

		// Make sure we have a unique name for file
		$dest_filename = uniqid() . '_' . basename($upload['name']);

		/* Start processing uploaded file */
		if (is_uploaded_file($src_file))
		{
			if (move_uploaded_file($src_file, $fullpath . '/' . $dest_filename))
			{
				$this->value = $fullpath . '/' . $dest_filename;
			}
			else
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_REDFORM_CANNOT_UPLOAD_FILE'), 'error');

				return false;
			}
		}

		return $this->value;
	}

	/**
	 * Return path to storage folder, create if necessary
	 *
	 * @return bool|string
	 */
	private function getStoragePath()
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
				JFactory::getApplication()->enqueueMessage(JText::_('COM_REDFORM_CANNOT_CREATE_FOLDER') . ': ' . $fullpath, 'error');

				return false;
			}
		}

		if (!is_writable($fullpath))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_REDFORM_PATH_NOT_WRITABLE') . ': ' . $fullpath, 'error');

			return false;
		}

		clearstatcache();

		return $fullpath;
	}
}
