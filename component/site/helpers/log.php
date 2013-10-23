<?php
/**
 * @package    Redform.Admin
 * @copyright  Redform (C) 2008-2013 redCOMPONENT.com
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Helper class for logging
 *
 * @package  Redform.Admin
 * @since    2.0
 */
class RedformHelperLog
{
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
	 * @return bool
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
}
