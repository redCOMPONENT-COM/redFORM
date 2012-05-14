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
 * Installation file
 */

/* ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

class com_redformInstallerScript
{
	public function postflight()
	{				
		/* Install plugin */
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		JFolder::copy(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_redform'.DS.'plugins'.DS.'content_redform', JPATH_SITE.DS.'tmp'.DS.'redform_plugin', '', true);
		JFile::move(JPATH_SITE.DS.'tmp'.DS.'redform_plugin'.DS.'redform.xm', JPATH_SITE.DS.'tmp'.DS.'redform_plugin'.DS.'redform.xml');
		$installer = new JInstaller();
		$installer->setAdapter('plugin');
		if (!$installer->install(JPATH_SITE.DS.'tmp'.DS.'redform_plugin')) {
			echo JText::_('COM_REDFORM_Plugin_install_failed') . $installer->getError().'<br />';
		}
		else {
			$db = &JFactory::getDbo();
			// autopublish the plugin
			$query = ' UPDATE #__extensions SET enabled = 1 WHERE name = '. $db->Quote('Content - redFORM');
			$db->setQuery($query);
			if ($db->query()) {
				echo JText::_('COM_REDFORM_Succesfully_installed_redform_content_plugin').'<br />';
			}
			else {
				echo JText::_('COM_REDFORM_Error_publishing_redform_content_plugin').'<br />';
			}
			 
		}
	}
}