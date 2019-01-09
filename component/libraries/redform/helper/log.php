<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Class RdfHelper log
 *
 * @package     Redform.Libraries
 * @subpackage  Helper
 * @since       2.5
 */
class RdfHelperLog
{
	static protected $logtime = array();

	/**
	 * Simple log
	 *
	 * @param   string  $comment  The comment to log
	 * @param   int     $userId   An optional user ID
	 *
	 * @return void
	 */
	public static function simpleLog($comment, $userId = 0)
	{
		JLog::addLogger(
			array('text_file' => 'com_redform.log'),
			JLog::DEBUG,
			'com_redform'
		);
		JLog::add($comment, JLog::DEBUG, 'com_redform');
	}

	/**
	 * Clear the logs
	 *
	 * @return boolean
	 */
	public static function clear()
	{
		$app = & JFactory::getApplication();

		$file = $app->getCfg('log_path') . '/com_redform.log';

		if (file_exists($file))
		{
			unlink($file);
		}

		return true;
	}

	public static function logTime($name, $label)
	{
		if (empty(self::$logtime[$name]))
		{
			self::$logtime[$name] = microtime(true);
		}

		self::simpleLog($label . ' ' . round(microtime(true) - self::$logtime[$name], 3));
	}
}
