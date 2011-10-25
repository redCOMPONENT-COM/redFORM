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
<fieldset>
<legend><?php echo JText::_('COM_REDFORM_FIELD_OPTION'); ?></legend>
		<table id="editvalue" class="adminform">
		<tr>
			<td class="hasTip" title="<?php echo JText::_('COM_REDFORM_FIELD_VALUE_LABEL').'::'.JText::_('COM_REDFORM_FIELD_VALUE_TIP');?>">
			<label for="value"><?php echo JText::_('COM_REDFORM_FIELD_VALUE_LABEL'); ?></label>
			</td>
			<td>
			<?php if ($this->row->fieldtype == 'info'): ?>
			<?php echo $this->editor->display( 'value',  $this->row->value, '100%;', '300', '75', '20', array('pagebreak', 'readmore') ) ; ?>
			<?php else: ?>
			<input class="inputbox" type="text" size="80" name="value" value="<?php echo $this->escape($this->row->value); ?>">
	    <?php endif; ?>
			</td>
		</tr>
		<tr>
			<td class="hasTip" title="<?php echo JText::_('COM_REDFORM_FIELD_LABEL_LABEL').'::'.JText::_('COM_REDFORM_FIELD_LABEL_TIP');?>">
			<label for="label"><?php echo JText::_('COM_REDFORM_FIELD_LABEL_LABEL'); ?></label>
			</td>
			<td>
			<input class="inputbox" type="text" size="80" name="label" value="<?php echo $this->escape($this->row->label); ?>">
			</td>
		</tr>
		<tr>
			<td class="hasTip" title="<?php echo JText::_('COM_REDFORM_FIELD_PRICE_LABEL').'::'.JText::_('COM_REDFORM_FIELD_PRICE_TIP');?>">
			<label for="price"><?php echo JText::_('COM_REDFORM_FIELD_PRICE_LABEL'); ?></label>
			</td>
			<td>
			<input type="text" name="price" value="<?php echo $this->row->price; ?>"/>
			</td>
		</tr>
		<tr id="trpublished">
			<td valign="top" align="right">
			<?php echo JText::_('COM_REDFORM_Published'); ?>
			</td>
			<td>
			<?php echo $this->lists['published']; ?>
			</td>
		</tr>
		</table>
</fieldset>

  <?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
	<input type="hidden" name="ordering" value="<?php echo $this->row->ordering; ?>" />
	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="ajaxsave" />
	<input type="hidden" name="controller" value="values" />
	<input type="hidden" name="form_id" value="<?php echo JRequest::getInt('form_id', 0); ?>" />
	<input type="hidden" name="field_id" value="<?php echo $this->field_id; ?>" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" id="listnameid" value="1">
	
	<input type="submit" name="submitbutton" value="submit"/> 
</form>