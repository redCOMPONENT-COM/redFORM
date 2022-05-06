<?php
/**
 * @since       __DEPLOY_VERSION__
 * @copyright   Copyright (C) 2016 - 2022 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Aesir.RedformJsonController.php
 */

namespace Redform\Controller;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;
use RdfCoreModelFormfield;
use Redform\Helper\FilesUpload;
use RuntimeException;
use Throwable;

defined('_JEXEC') or die;

/**
 * Class RedformJsonController
 * @since  __DEPLOY_VERSION__
 */
class RedformJsonController extends \JControllerLegacy
{
	/**
	 * @return void
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	public function remove(): void
	{
		$app = Factory::getApplication();

		try
		{
			$input    = $app->input;
			$fileName = $input->getString('file_name', '');
			$token    = $input->getString('token', '');
			$formId   = $input->getInt('form_id', 0);

			if (!FilesUpload::fileTokenIsValid($token, $fileName, $formId))
			{
				throw new RuntimeException('Permission Denied');
			}

			$tmp = Factory::getConfig()->get('tmp_path') . '/redform/filesupload/';

			if (File::exists($tmp . $fileName))
			{
				File::delete($tmp . $fileName);
			}

			$fullPath = JPATH_SITE . '/' . (new FilesUpload)->getStoragePath($formId);

			if (File::exists($fullPath . $fileName))
			{
				File::delete($fullPath . $fileName);
			}

			echo new JsonResponse('ok');
		}
		catch (Throwable $e)
		{
			echo new JsonResponse($e);
		}

		$app->close();
	}

	/**
	 * @return void
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	public function upload(): void
	{
		$app = Factory::getApplication();

		try
		{
			if (!Session::checkToken())
			{
				throw new RuntimeException(Text::_('JINVALID_TOKEN'));
			}

			$input   = $app->input;
			$fieldId = $input->getInt('field_id');

			if (empty($fieldId))
			{
				throw new RuntimeException('Request is wrong!');
			}

			$data = (new RdfCoreModelFormfield)->setId($fieldId)->getItem();

			if (empty($data))
			{
				throw new RuntimeException('Request is wrong!');
			}

			$params = new Registry($data->params);
			$files  = $input->files->get('file', [], 'array');
			$uuid   = $input->get('uuid', [], 'array');
			$formId = $input->getInt('form_id', 0);

			if (empty($files))
			{
				return;
			}

			$data = [];
			$tmp  = Factory::getConfig()->get('tmp_path') . '/redform/filesupload/';

			if (!Folder::exists($tmp))
			{
				Folder::create($tmp);
			}

			$acceptedFiles = explode(',', $params->get('accepted_files', 'image/*'));

			foreach ($files as $key => $file)
			{
				if (((int) $params->get('maxsize') * 1024 * 1024) < $file['size'])
				{
					throw new RuntimeException(
						str_replace(
							[
								'{{filesize}}',
								'{{maxFilesize}}',
							],
							[
								$file['size'],
								$params->get('maxsize'),
							],
							Text::_('LIB_REDFORM_FILES_UPLOAD_DICTFILETOOBIG')
						)
					);
				}

				if (!$this->isAcceptedFile($file, $acceptedFiles))
				{
					throw new RuntimeException(
						Text::_('LIB_REDFORM_FILES_UPLOAD_DICTINVALIDFILETYPE')
					);
				}

				$file['uuid'] = $uuid[$key];
				$extension    = pathinfo($file['name'], PATHINFO_EXTENSION);
				$tmpName      = $file['uuid'] . ($extension ? ('.' . $extension) : '');
				$tmpDest      = $tmp . $tmpName;

				if (!File::upload($file['tmp_name'], $tmpDest))
				{
					throw new RuntimeException(
						'Error file upload'
					);
				}

				$file['tmp_name']    = $tmpName;
				$file['token']       = FilesUpload::getTokenFile($tmpName, $formId);
				$file['form_id']     = $formId;
				$file['base64']      = base64_encode(json_encode($file));
				$data[$file['uuid']] = $file;
			}

			echo new JsonResponse($data);
		}
		catch (Throwable $e)
		{
			echo new JsonResponse($e);
		}

		$app->close();
	}

	/**
	 * @param   array  $file           File
	 * @param   array  $listOfAccepts  List of accepts
	 *
	 * @return boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected function isAcceptedFile(array $file, array $listOfAccepts): bool
	{
		if (empty($listOfAccepts))
		{
			return true;
		}

		$baseMimeType = preg_replace('/\/.*$/', '$1', $file['type']);

		foreach ($listOfAccepts as $acceptedFile)
		{
			$acceptedFile = trim($acceptedFile);

			if ($acceptedFile[0] === '.')
			{
				if ($this->endsWith(strtolower($acceptedFile), $file['name']))
				{
					return true;
				}
			}
			elseif (preg_match('/\/\*$/', $acceptedFile, $output))
			{
				if ($baseMimeType === preg_replace('/\/\*$/', '$1', $acceptedFile))
				{
					return true;
				}
			}
			else
			{
				if ($acceptedFile === $file['type'])
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @param   string  $haystack  Haystack
	 * @param   string  $needle    Needle
	 *
	 * @return boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected function endsWith(string $haystack, string $needle): bool
	{
		$length = strlen($needle);

		if (!$length)
		{
			return true;
		}

		return substr($haystack, -$length) === $needle;
	}
}
