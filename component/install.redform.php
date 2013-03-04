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
    private $installed_mods             = array();
    private $installed_plugs            = array();

    /**
     * method to run after an install/update/uninstall method
     *
     * @return void
     */
    public function postflight($action, $parent)
	{
		$this->installModsPlugs($parent);
		if (count($this->installed_plugs)){
			echo '<div>
                          <table class="adminlist" cellspacing="1">
                            <thead>
                                <tr>
                                    <th>'.JText::_('Plugin').'</th>
                                    <th>'.JText::_('Group').'</th>
                                    <th>'.JText::_('Status').'</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <td colspan="3">&nbsp;</td>
                                </tr>
                            </tfoot>
                            <tbody>';
			foreach ($this->installed_plugs as $plugin) :
			$pstatus    = ($plugin['upgrade']) ? JHtml::_('image','admin/tick.png', '', NULL, true) : JHtml::_('image','admin/publish_x.png', '', NULL, true);
			echo '<tr>
                                            <td>'.$plugin['plugin'].'</td>
                                            <td>'.$plugin['group'].'</td>
                                            <td style="text-align: center;">'.$pstatus.'</td>
                                          </tr>';
			endforeach;
			echo '   </tbody>
                         </table>
                         </div>';
		}

		if (count($this->installed_mods)){
			echo '<div>
                          <table class="adminlist" cellspacing="1">
                            <thead>
                                <tr>
                                    <th>'.JText::_('Module').'</th>
                                    <th>'.JText::_('Status').'</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                            </tfoot>
                            <tbody>';
			foreach ($this->installed_mods as $module) :
			$mstatus    = ($module['upgrade']) ? JHtml::_('image','admin/tick.png', '', NULL, true) : JHtml::_('image','admin/publish_x.png', '', NULL, true);
			echo '<tr>
                                            <td>'.$module['module'].'</td>
                                            <td style="text-align: center;">'.$mstatus.'</td>
                                          </tr>';
			endforeach;
			echo '   </tbody>
                         </table>
                         </div>';
		}
// 		/* Install plugin */
// 		jimport('joomla.filesystem.file');
// 		jimport('joomla.filesystem.folder');
// 		JFolder::copy(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_redform'.DS.'plugins'.DS.'content_redform', JPATH_SITE.DS.'tmp'.DS.'redform_plugin', '', true);
// 		JFile::move(JPATH_SITE.DS.'tmp'.DS.'redform_plugin'.DS.'redform.xm', JPATH_SITE.DS.'tmp'.DS.'redform_plugin'.DS.'redform.xml');
// 		$installer = new JInstaller();
// 		$installer->setAdapter('plugin');
// 		if (!$installer->install(JPATH_SITE.DS.'tmp'.DS.'redform_plugin')) {
// 			echo JText::_('COM_REDFORM_Plugin_install_failed') . $installer->getError().'<br />';
// 		}
// 		else {
// 			$db = &JFactory::getDbo();
// 			// autopublish the plugin
// 			$query = ' UPDATE #__extensions SET enabled = 1 WHERE name = '. $db->Quote('Content - redFORM');
// 			$db->setQuery($query);
// 			if ($db->query()) {
// 				echo JText::_('COM_REDFORM_Succesfully_installed_redform_content_plugin').'<br />';
// 			}
// 			else {
// 				echo JText::_('COM_REDFORM_Error_publishing_redform_content_plugin').'<br />';
// 			}

// 		}
	}

	protected function installModsPlugs($parent)
	{
		$manifest       = $parent->get("manifest");
		$parent         = $parent->getParent();
		$source         = $parent->getPath("source");

		//**********************************************************************
		// DO THIS IF WE DECIDE TO AUTOINSTALL PLUGINS/MODULES
		//**********************************************************************
		// install plugins and modules
		$installer = new JInstaller();

		// Install plugins
		foreach($manifest->plugins->plugin as $plugin) {
			$attributes                 = $plugin->attributes();
			$plg                        = $source . '/' . $attributes['folder'].'/'.$attributes['plugin'];
// 			echo '<pre>';print_r($plg); echo '</pre>';exit;
			$new                        = ($attributes['new']) ? '&nbsp;(<span class="green">New in v.'.$attributes['new'].'!</span>)' : '';
			if($installer->install($plg)){
				$this->installed_plugs[]    = array('plugin' => $attributes['plugin'].$new, 'group'=> $attributes['group'], 'upgrade' => true);
			}else{
				$this->installed_plugs[]    = array('plugin' => $attributes['plugin'], 'group'=> $attributes['group'], 'upgrade' => false);
				$this->iperror[] = JText::_('Error installing plugin').': '.$attributes['plugin'];
			}
		}
		return true;

		// Install modules
		foreach($manifest->modules->module as $module) {
			$attributes             = $module->attributes();
			$mod                    = $source . '/' . $attributes['folder'].'/'.$attributes['module'];
			$new                    = ($attributes['new']) ? '&nbsp;(<span class="green">New in v.'.$attributes['new'].'!</span>)' : '';
			if($installer->install($mod)){
				$this->installed_mods[] = array('module' => $attributes['module'].$new, 'upgrade' => true);
			}else{
				$this->installed_mods[] = array('module' => $attributes['module'], 'upgrade' => false);
				$this->iperror[] = JText::_('Error installing module').': '.$attributes['module'];
			}
		}
	}
}