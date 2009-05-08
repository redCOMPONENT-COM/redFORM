<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com
 *
 * Un-installation file
 */

/* ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

function com_uninstall(){
	/* Remove the plugin */
	jimport('joomla.filesystem.file');
	jimport('joomla.filesystem.folder');
	$database = JFactory::getDBO();
	JFile::delete(JPATH_SITE.DS.'plugins'.DS.'content'.DS.'redform.xml');
	JFile::delete(JPATH_SITE.DS.'plugins'.DS.'content'.DS.'redform.php');
	$langfiles = JFolder::files(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_redform'.DS.'plugins'.DS.'language');
	$basefolder = JPATH_SITE.DS.'language';
	foreach ($langfiles as $key => $langfile) {
		$lang = substr($langfile, 0, 5);
		if (JFolder::exists($basefolder.DS.$lang)) {
			JFile::delete($basefolder.DS.$lang.DS.$langfile);
		}
	}
	
	$query = "DELETE FROM #__plugins WHERE folder = 'content' AND element = 'redform'";
	$database->setQuery($query);
	$database->query();
}
?>
