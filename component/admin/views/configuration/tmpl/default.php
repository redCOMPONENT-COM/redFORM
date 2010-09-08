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

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport('joomla.html.pane'); 
JHTML::_('behavior.tooltip');
?>
<form action="index.php" method="post" name="adminForm">
	<?php $pane = JPane::getInstance('tabs');
	$row = 0;
	echo $pane->startPane("settings");
	echo $pane->startPanel( JText::_('FILES'), 'files_tab' );
	?>
	<table class="adminform">
		<tr class="row<?php echo $row; ?>">
			<td width="15%">
			<?php echo JHTML::tooltip(JText::_('REDFORM_FILES_TIP'), JText::_('REDFORM_FILES'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('REDFORM_FILES'); ?>
			</td>
			<td>
			<input type="text" id="filelist_path" name="configuration[filelist_path]" size="120" value="<?php echo $this->configuration['filelist_path']->value;?>"></input>
			</td>
		</tr>
	</table>
	<?php
	echo $pane->endPanel();
	echo $pane->endPane();
	?>
	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="configuration" />
</form>
