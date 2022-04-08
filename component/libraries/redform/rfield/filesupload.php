<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Rfield
 * @copyright   Copyright (c) 2008 - 2022 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Uri\Uri;
use Redform\Helper\FilesUpload;

defined('_JEXEC') or die;

/**
 * redFORM field
 * @since       __DEPLOY_VERSION__
 * @subpackage  Rfield
 * @package     Redform.Libraries
 */
class RdfRfieldFilesupload extends RdfRfield
{
	/**
	 * @var string
	 */
	protected $type = 'filesupload';

	/**
	 * Set field value, try to look up if null
	 *
	 * @param   string  $value   value
	 * @param   bool    $lookup  set true to lookup for a default value if value is null
	 *
	 * @return string new value
	 */
	public function setValue($value, $lookup = false)
	{
		if ($value && !is_array($value))
		{
			$newValue = [];

			foreach (explode('~~~', $value) as $val)
			{
				$decoded = json_decode(base64_decode($val), true);

				$newValue[$decoded['uuid']] = $val;
			}
		}
		else
		{
			$newValue = $value;
		}

		return parent::setValue($newValue, $lookup);
	}

	/**
	 * Get and set the value from post data, using appropriate filtering
	 *
	 * @param   int  $signup  form instance number for the field
	 *
	 * @return mixed
	 */
	public function getValueFromPost($signup)
	{
		$value = $this->getFileUpload($signup);

		if ($value)
		{
			$this->value = $value;
		}

		return $this->value;
	}

	/**
	 * Returns field value ready to be printed.
	 * Array values will be separated with separator (default '~~~')
	 *
	 * @param   string  $separator  separator
	 *
	 * @return string
	 */
	public function getValueAsString($separator = '~~~')
	{
		if (!empty($this->value))
		{
			if (is_string($this->value))
			{
				$value = explode($separator, $this->value);
			}
			else
			{
				$value = $this->value;
			}

			$return = [];

			foreach ($value as $item)
			{
				$decoded = json_decode(base64_decode($item), true);

				$return[] = Uri::root() . $decoded['path'];
			}

			return implode($separator, $return);
		}

		return '';
	}

	/**
	 * Check if there was a file uploaded
	 *
	 * @param   int  $signup  signup id
	 *
	 * @return mixed
	 * @throws RuntimeException
	 */
	private function getFileUpload($signup)
	{
		$path = (new FilesUpload)
			->getStoragePath($this->load()->form_id);

		if (!$path)
		{
			return false;
		}

		$fullpath = JPATH_SITE . '/' . $path;
		$key      = $this->getPostName($signup);
		$input    = Factory::getApplication()->input;
		$uploaded = $input->get($key, [], 'array');

		if (empty($uploaded))
		{
			return false;
		}

		$tmp = Factory::getConfig()
				->get('tmp_path') . '/redform/filesupload/';

		$this->value = [];

		foreach ($uploaded as $uuid => $data)
		{
			$decoded = json_decode(base64_decode($data), true);

			if (!empty($decoded['tmp_name']))
			{
				if (File::exists($fullpath . $decoded['tmp_name']))
				{
					File::delete($fullpath . $decoded['tmp_name']);
				}

				File::move(
					$tmp . $decoded['tmp_name'],
					$fullpath . $decoded['tmp_name']
				);

				$decoded['path']        = $path . $decoded['tmp_name'];
				$decoded['stored_name'] = $decoded['tmp_name'];
				unset($decoded['tmp_name']);
			}

			$this->value[$uuid] = base64_encode(json_encode($decoded));
		}

		return $this->value;
	}
}
