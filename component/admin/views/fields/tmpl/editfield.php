<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );
JHTML::_('behavior.tooltip');
?>

<form action="index.php" method="post" name="adminForm">
		<table class="adminform">
		<tr>
			<td>
			<?php echo JHTML::tooltip(JText::_('Enter the field here'), JText::_('Field'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Field'); ?>
			</td>
			<td>
			<input class="inputbox" type="text" size="80" name="field" value="<?php echo $this->row->field; ?>">
			</td>
		</tr>
		<tr>
			<td>
			<?php echo JHTML::tooltip(JText::_('ENTER_TOOLTIP_INFO'), JText::_('ENTER_TOOLTIP'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('ENTER_TOOLTIP'); ?>
			</td>
			<td>
				<textarea name="tooltip" cols="80" rows="5"><?php echo $this->row->tooltip; ?></textarea>
			</td>
		</tr>
		<tr>
			<td>
			<?php echo JHTML::tooltip(JText::_('Set to yes to check if the field is filled in'), JText::_('Validate'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Validate'); ?>
			</td>
			<td>
			<?php echo $this->lists['validate']; ?>
			</td>
		</tr>
		<tr>
			<td>
			<?php echo JHTML::tooltip(JText::_('Set to yes to make the field unique, it can only appear once in the database. For example, only allow 1 registration per e-mail address.'), JText::_('Unique'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Unique'); ?>
			</td>
			<td>
			<?php echo $this->lists['unique']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right">
			<?php echo JHTML::tooltip(JText::_('Select the form the field belongs to. Changing forms REMOVES the field from the old form including all data.'), JText::_('Form'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Form'); ?>
			</td>
			<td>
			<?php echo $this->lists['forms']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right">
			<?php echo JHTML::tooltip(JText::_('Set to Yes to make the field show on the form'), JText::_('Published'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Published'); ?>
			</td>
			<td>
			<?php echo $this->lists['published']; ?>
			</td>
		</tr>
		</table>
	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
	<?php if ($this->state == 'disabled') { ?><input type="hidden" name="form_id" value="<?php echo $this->form_id; ?>" /><?php } ?>
	<input type="hidden" name="ordering" value="<?php echo $this->row->ordering; ?>" />
	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="fields" />
	<input type="hidden" name="controller" value="fields" />
</form>