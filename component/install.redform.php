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
	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent)
	{
		if ($type == 'update')
		{
			// get db version
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('s.version_id');
			$query->from('#__extensions AS e');
			$query->join('INNER', '#__schemas AS s ON s.extension_id = e.extension_id');
			$query->where('e.element = '.$db->Quote('com_redform'));
			$db->setQuery($query);
			$version = $db->loadResult();

			if (version_compare("2.0.b.5.0", $version))
			{
				$fields = $db->getTableColumns('#__rwf_values');
				if (!isset($fields['price']))
				{
					$query = ' ALTER TABLE #__rwf_values ADD '.$db->nameQuote('price') . ' DOUBLE NULL DEFAULT NULL ';
					$db->setQuery($query);
					try {
						$db->query();
					}
					catch (Exception $e) {
						echo $e->getMessage();
					}
				}
			}
		}
	}

	public function postflight()
	{
		/* Install plugin */
		$plg = JPATH_SITE.'/administrator/components/com_redform/plugins/content_redform';

		$db = JFactory::getDbo();
		$installer = new JInstaller();

		if ($installer->install($plg))
		{
			// autopublish the plugin
			$query = ' UPDATE #__extensions SET enabled = 1 WHERE folder = '. $db->Quote('content') . ' AND element = '.$db->Quote('redform');
			$db->setQuery($query);

			if ($db->query())
			{
				echo JText::_('COM_REDFORM_Succesfully_installed_redform_content_plugin').'<br />';
			}
			else
			{
				echo JText::_('COM_REDFORM_Error_publishing_redform_content_plugin').'<br />';
			}
		}
		else
		{
			echo JText::_('COM_REDFORM_Plugin_install_failed') . $installer->getError().'<br />';
		}
	}
}