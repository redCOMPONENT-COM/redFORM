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
<form action="index.php" method="post" name="adminForm">

	<p><?php echo JText::_('COM_REDFORM_FIELDS_COPY_CHOSE_DESTINATION_INTRO'); ?></p>
	<ul>
	<?php foreach ($this->fields as $f): ?>
		<li><?php echo JText::sprintf('COM_REDFORM_FIELDS_COPY_FIELD_S_TYPE_S_FROM_FORM_S', $f->field, $f->fieldtype, $f->formname); ?></li>
	<?php endforeach; ?>		
	</ul>
	<p><label for="form_id"><?php echo JText::_('COM_REDFORM_FIELDS_COPY_CHOSE_DESTINATION_LABEL'); ?></label> <?php echo $this->lists['form_id']; ?></p>
	<input type="hidden" name="cids" value="<?php echo implode(',',$this->cids); ?>" />
	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="fields" />
</form>