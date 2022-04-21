<?php
/**
 * @since       __DEPLOY_VERSION__
 * @copyright   Copyright (C) 2016 - 2022 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Aesir.FilesUpload.php
 */

namespace Redform\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use RuntimeException;

defined('_JEXEC') or die;

/**
 * Class FilesUpload
 * @since   __DEPLOY_VERSION__
 * @package Redform\Helper
 */
class FilesUpload
{
	/**
	 * @param   string  $fileName  File name
	 * @param   int     $formId    Form Id
	 *
	 * @return string
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getTokenFile(string $fileName, int $formId): string
	{
		return md5($formId . '-' . $fileName . '-' . Factory::getConfig()->get('secret'));
	}

	/**
	 * @param   string  $tokenToCheck  Token to check
	 * @param   string  $fileName      File name
	 * @param   int     $formId        Form Id
	 *
	 * @return boolean
	 * @since  __DEPLOY_VERSION__
	 */
	public static function fileTokenIsValid(string $tokenToCheck, string $fileName, int $formId): bool
	{
		return self::getTokenFile($fileName, $formId) === $tokenToCheck;
	}

	/**
	 * Return path to storage folder, create if necessary
	 *
	 * @param   integer  $formId  Form id
	 *
	 * @return boolean|string
	 */
	public function getStoragePath(int $formId): string
	{
		$params = ComponentHelper::getParams('com_redform');
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true)
			->select('f.formname')
			->from('#__rwf_forms AS f')
			->where('f.id = ' . $db->q($formId));
		$formname = $db->setQuery($query)
			->loadResult();

		$filepath = trim($params->get('upload_path', 'images/redform'), '/')
			. '/' . File::makeSafe(str_replace(' ', '', $formname));
		$fullpath = JPATH_SITE . '/' . $filepath;

		if (!Folder::exists($fullpath))
		{
			if (!Folder::create($fullpath))
			{
				throw new RuntimeException(Text::_('COM_REDFORM_CANNOT_CREATE_FOLDER') . ': ' . $fullpath);
			}
		}

		if (!is_writable($fullpath))
		{
			throw new RuntimeException(Text::_('COM_REDFORM_PATH_NOT_WRITABLE') . ': ' . $fullpath);
		}

		clearstatcache();

		return $filepath . '/';
	}
}
