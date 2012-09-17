<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license GNU/GPL, see LICENSE.php
 * redFORM can be downloaded from www.redcomponent.com
 * redFORM is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redFORM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redFORM; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Helper class for logging
 * @package    Notes
 * @subpackage com_notes
 */
class RedformHelperLog
{
	/**
	 * Simple log
	 * @param string $comment  The comment to log
	 * @param int $userId      An optional user ID
	 */
	function simpleLog($comment, $userId = 0)
	{
		JLog::addLogger(
			array('text_file' => 'com_redform.log.php'),
			JLog::DEBUG,
			'com_redform'		
		);
		JLog::add($comment, JLog::DEBUG, 'com_redform');
	}


	function clear()
	{
		$app = & JFactory::getApplication();

		$file = $app->getCfg('log_path').DS.'com_redform.log.php';
		if (file_exists($file)) {
			unlink($file);
		}
		return true;
	}
}
