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
<form action="index.php" method="post" name="adminForm" id="adminForm">
		<table id="editvalue" class="adminform">
		<tr>
			<td>
			<?php echo JText::_('COM_REDFORM_Value'); ?>
			</td>
			<td>
			<?php if ($this->row->fieldtype == 'info'): ?>
			<?php echo $this->editor->display( 'value',  $this->row->value, '100%;', '300', '75', '20', array('pagebreak', 'readmore') ) ; ?>
			<?php else: ?>
			<input class="inputbox" type="text" size="80" name="value" value="<?php echo $this->escape($this->row->value); ?>">
	    <?php endif; ?>
			<?php echo JHTML::tooltip(JText::_('COM_REDFORM_Enter_the_value_here'), JText::_('COM_REDFORM_Value'), 'tooltip.png', '', '', false); ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right">
			<?php echo JText::_('COM_REDFORM_Field'); ?>
			</td>
			<td>
			<?php echo $this->lists['fields']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right">
			<?php echo JText::_('COM_REDFORM_Price'); ?>
			</td>
			<td>
			<input type="text" name="price" value="<?php echo $this->row->price; ?>"/>
			<?php echo JHTML::tooltip(JText::_('COM_REDFORM_VALUE_PRICE_TIP'), JText::_('COM_REDFORM_Price'), 'tooltip.png', '', '', false); ?>
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
  <?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
	<input type="hidden" name="ordering" value="<?php echo $this->row->ordering; ?>" />
	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="values" />
	<input type="hidden" name="form_id" value="<?php echo JRequest::getInt('form_id', 0); ?>" />
	<input type="hidden" id="listnameid" value="1">
</form>
