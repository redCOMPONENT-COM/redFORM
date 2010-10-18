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
	$langfiles = JFolder::files(JPATH_SITE.DS.'administrator'.DS.'language', '.*content_redform.*', true, true);
	foreach ((array) $langfiles as $key => $langfile) {
		JFile::delete($langfiles);
	}
	
	$query = "DELETE FROM #__plugins WHERE folder = 'content' AND element = 'redform'";
	$database->setQuery($query);
	$database->query();
}
?>
