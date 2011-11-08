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
JHTML::_('behavior.tooltip');
?>
<form action="index.php" method="post" name="adminForm" enctype="multipart/form-data">

	<fieldset class="adminform">
	<legend><?php echo JText::_('COM_REDFORM_IMPORT'); ?></legend>
	<p><?php echo JText::_('COM_REDFORM_SUBMITTERS_IMPORT_CHOSE_DESTINATION_FORM_INTRO'); ?></p>
	<table class="admintable">
		<tr>
			<td class="key"><label for="form_id"><?php echo JText::_('COM_REDFORM_SUBMITTERS_IMPORT_CHOSE_DESTINATION_FORM_LABEL'); ?></label></td>
			<td><?php echo $this->lists['form_id']; ?></td>
		</tr>
		<tr>
			<td class="key"><label for="importfile"><?php echo JText::_('COM_REDFORM_SUBMITTERS_IMPORT_FILE_LABEL'); ?></label></td>
			<td><input type="file" name="importfile" /></td>
		</tr>
	</table>
	</fieldset>
	
	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="submitters" />
</form>