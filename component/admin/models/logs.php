<?php
/**
 * @package     Redform.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Logs Model
 *
 * @package     Redform.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedformModelLogs extends RModelAdmin
{
	/**
	 * Method to get items list
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function getItems()
	{
		$app = JFactory::getApplication();

		$contents = '';
		$file = $app->getCfg('log_path') . '/com_redform.log';

		if (file_exists($file))
		{
			$handle = fopen($file, "r");

			if (!$handle)
			{
				throw new Exception('error opening: ' . $file, 500);
			}

			while (!feof($handle))
			{
				$contents .= fread($handle, 8192);
			}

			fclose($handle);
		}

		if (empty($contents))
		{
			$contents = array(JText::_('COM_REDFORM_No_log'));
		}
		else
		{
			$contents = explode("\n", $contents);
			array_shift($contents);
		}

		return $contents;
	}
}
