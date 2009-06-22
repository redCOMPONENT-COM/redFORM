<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
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
        // Include the library dependancies
        jimport('joomla.error.log');
        $options = array(
            'format' => "{DATE}\t{TIME}\t{USER_ID}\t{COMMENT}"
        );
        // Create the instance of the log file in case we use it later
        $log = &JLog::getInstance('com_redform.log', $options);
        $log->addEntry(array('comment' => $comment, 'user_id' => $userId));
    }
    

    function clear()
    {
      $app = & JFactory::getApplication();
      
      $file = $app->getCfg('log_path').DS.'com_redform.log';
      if (file_exists($file)) {
        unlink($file);
      }
      return true;
    }
}
?>