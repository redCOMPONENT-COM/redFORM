<?php
/**
 * @package     Redform.Library
 * @subpackage  CLI
 *
 * @copyright   Copyright (C) 2008 - 2022 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\Application\CliApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/../../defines.php'))
{
	require_once dirname(__DIR__) . '/../../defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__) . '/../..');
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Import the configuration.
require_once JPATH_CONFIGURATION . '/configuration.php';

define('JDEBUG', 0);

$_SERVER['REQUEST_METHOD'] = 'GET';

define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_redform');

/**
 * @package     Aesir.E-Commerce
 * @subpackage  Create_Pdf_Productsheet
 *
 * @since       1.13.0
 */
class ClearTmpFileUploadsFolderApplicationCli extends CliApplication
{
	/**
	 * A method to start the cli script
	 * Optional parameters: [1]: SKU of the product to print (only this product) - no Redis used in this case
	 *
	 * @return void
	 * @since  1.13.0
	 * @throws Exception
	 */
	public function doExecute()
	{
		Factory::getApplication('site');

		$tmp = Factory::getConfig()->get('tmp_path') . '/redform/filesupload/';

		if (!Folder::exists($tmp))
		{
			return;
		}

		$list = Folder::files($tmp);

		if (empty($list))
		{
			return;
		}

		$nowMinusDay = (new DateTime)
			->modify('-1 day');

		foreach ($list as $item)
		{
			$filename = $tmp . $item;

			$dateTime = new DateTime('@' . filemtime($filename));

			if ($nowMinusDay >= $dateTime)
			{
				File::delete($filename);
			}
		}
	}
}

CliApplication::getInstance('ClearTmpFileUploadsFolderApplicationCli')
	->execute();
