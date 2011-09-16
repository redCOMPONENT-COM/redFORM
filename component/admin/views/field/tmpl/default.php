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

<script type="text/javascript">

	window.addEvent('domready', function(){
		$('fieldtype').addEvent('click', function(){
			if ($('form_id').value == 0) {
				alert("<?php echo JText::_('REDFORM_FIELD_JS_PLEASE_SELECT_FORM_FIRST'); ?>");
			}
		});
		
		$('fieldtype').addEvent('change', function(){
			if (confirm("<?php echo JText::_('REDFORM_FIELD_JS_CONFIRM_CHANGE_TYPE'); ?>")) {
				submitbutton('apply');
			}
		});

		var mailingremove = $$('.listname-delete');
		if (mailingremove)
		{
			mailingremove.each(function(el) {
				el.addEvent('click', removeMailingListField);
			});
		}

	});
	// language strings for ajaxvalues.js
	var edittext = "<?php echo JText::_('COM_REDEVENT_EDIT'); ?>";
	var deletetext = "<?php echo JText::_('COM_REDEVENT_DELETE'); ?>";
	var textyes = "<?php echo JText::_('YES'); ?>";
	var textno = "<?php echo JText::_('NO'); ?>";
	var textup = "<?php echo JText::_('UP'); ?>";
	var textdown = "<?php echo JText::_('DOWN'); ?>";

	function addMailingListField()
	{
		var newrow = $$('.listname-row')[0].clone();
		newrow.getElement('input[name^=listname]').value = '';
		newrow.getElement('.listname-delete').addEvent('click', removeMailingListField);
		newrow.injectInside($('mailinglist-table'));
	}

	function removeMailingListField()
	{
		var countfields = $$('.listname-row').length;
		if (countfields > 1) {
			this.getParent().getParent().remove();
		}		
	}
	
	function submitbutton(pressbutton)
	{
		var form = document.adminForm;
		
		if (pressbutton == 'cancel') {
			submitform( pressbutton );
			return;
		}

		// do field validation
		if (form.field.value == ""){
			alert( "<?php echo JText::_( 'COM_REDFORM_FIELD_NAME_REQUIRED' ); ?>" );
		} else {
			submitform( pressbutton );
		}
	}
</script>

<form action="index.php" method="post" name="adminForm">

		<table class="adminform">
		<tr>
			<td>
			<label for="field"><?php echo JHTML::tooltip(JText::_('Enter the field here'), JText::_('Field'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Field'); ?></label>
			</td>
			<td>
			<input class="inputbox" type="text" size="80" name="field" value="<?php echo $this->row->field; ?>"/>
			</td>
		</tr>
		<tr>
			<td>
			<label for="field_header"><?php echo JHTML::tooltip(JText::_('COM_REDEVENT_FIELD_FORM_FIELD_HEADER_DESC'), JText::_('COM_REDEVENT_FIELD_FORM_FIELD_HEADER_LABEL'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('COM_REDEVENT_FIELD_FORM_FIELD_HEADER_LABEL'); ?></label>
			</td>
			<td>
			<input class="inputbox" type="text" size="80" name="field_header" value="<?php echo $this->row->field_header; ?>"/>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right">
			<label for="form"><?php echo JHTML::tooltip(JText::_('Select the form the field belongs to. Changing forms REMOVES the field from the old form including all data.'), JText::_('Form'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Form'); ?></label>
			</td>
			<td>
			<?php echo $this->lists['forms']; ?>
			</td>
		</tr>
		<tr id="trfieldtypes">
			<td valign="top" align="right">
			<label for="fieldtype"><?php echo JText::_('Field type'); ?></label>
			</td>
			<td>
			<div id="newfieldtype">
			<?php echo $this->lists['fieldtypes']; ?>
			</div>
			</td>
		</tr>
		<tr>
			<td>
			<label for="tooltip"><?php echo JHTML::tooltip(JText::_('ENTER_TOOLTIP_INFO'), JText::_('ENTER_TOOLTIP'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('ENTER_TOOLTIP'); ?></label>
			</td>
			<td>
				<textarea name="tooltip" cols="80" rows="5"><?php echo $this->row->tooltip; ?></textarea>
			</td>
		</tr>
		<tr>
			<td>
			<label for="required"><?php echo JHTML::tooltip(JText::_('Set to yes to check if the field is filled in'), JText::_('Required'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Required'); ?></label>
			</td>
			<td>
			<?php echo $this->lists['validate']; ?>
			</td>
		</tr>
		<tr>
			<td>
			<label for="unique"><?php echo JHTML::tooltip(JText::_('Set to yes to make the field unique, it can only appear once in the database. For example, only allow 1 registration per e-mail address.'), JText::_('Unique'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Unique'); ?></label>
			</td>
			<td>
			<?php echo $this->lists['unique']; ?>
			</td>
		</tr>
		<tr>
			<td>
			<label for="readonly"><?php echo JHTML::tooltip(JText::_('COM_REDFORM_FIELD_READONLY_TIP'), JText::_('COM_REDFORM_FIELD_READONLY'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('COM_REDFORM_FIELD_READONLY'); ?></label>
			</td>
			<td>
			<?php echo JHTML::_('select.booleanlist', 'readonly', '', $this->row->readonly); ?>
			</td>
		</tr>
		<tr>
			<td>
			<label for="default"><?php echo JHTML::tooltip(JText::_('COM_REDFORM_FIELD_DEFAULT_VALUE_TIP'), JText::_('COM_REDFORM_FIELD_DEFAULT_VALUE'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('COM_REDFORM_FIELD_DEFAULT_VALUE'); ?></label>
			</td>
			<td>
			<textarea name="default" cols="80" rows="2"><?php echo $this->row->default; ?></textarea>
			</td>
		</tr>
		<?php if (REDMEMBER_INTEGRATION): ?>
		<tr>
			<td valign="top" align="right">
			<label for="redmember_field"><?php echo JHTML::tooltip(JText::_('COM_REDFORM_SELECT_REDMEMBER_FIELD'), JText::_('COM_REDFORM_REDMEMBER_FIELD'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Redmember field'); ?></label>
			</td>
			<td>
			<?php echo $this->lists['rmfields']; ?>
			</td>
		</tr>
		<?php endif; ?>
		
		<tr>
			<td valign="top" align="right">
			<label for="published"><?php echo JHTML::tooltip(JText::_('Set to Yes to make the field show on the form'), JText::_('Published'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Published'); ?></label>
			</td>
			<td>
			<?php echo $this->lists['published']; ?>
			</td>
		</tr>
		</table>
		
		
		<?php if (isset($this->displaymailinglist) && $this->displaymailinglist): 
			$listnames = explode(';', $this->mailinglist->listnames);
			?>
		<fieldset class="adminform">
		<legend><?php echo JText::_('COM_REDFORM_FIELD_EDIT_MAILINGLIST_FIELDSET')?></legend>
	
		<table class="admintable" id="mailinglist-table">
			<tr id="trmailinglist">
				<td class="key hasTip" title="<?php echo JText::_('NEWSLETTERS').'::'.JText::_('NEWSLETTERS_TIP'); ?>">
					<?php echo JText::_('NEWSLETTERS'); ?>
				</td>
				<td>
					<div id="newmailinglist">
						<?php echo $this->lists['mailinglists']; ?>
					</div>
				</td>
			</tr>
			<tr id="traddlists">
				<td class="key hasTip" title="<?php echo JText::_('ADD_LISTS').'::'.JText::_('ADD_LISTS_TIP'); ?>">
					<?php echo JText::_('ADD_LISTS'); ?>
				</td>
				<td>
					<a href="#" onClick="addMailingListField(); return false;"><?php echo JText::_('ADD_LIST'); ?></a>
				</td>
			</tr>
			<?php
				foreach ($listnames as $key => $name): ?>
					<tr class="listname-row">
						<td class="key hasTip" title="<?php echo JText::_('LISTNAME').'::'.JText::_('LISTNAME_TIP'); ?>">
							<?php echo JText::_('LISTNAME'); ?>
						</td>
						<td>
							<input type="text" name="listname[]" value="<?php echo $name; ?>"/>&nbsp;
							<a href="#" class="listname-delete"><?php echo JText::_('REMOVE_LIST'); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
		</table>
				
		</fieldset>					
		<?php endif ;	?>
		
		<!-- Values table -->
		<fieldset class="adminform" id="field-options" style="display:none;">
		<legend><?php echo JText::_('COM_REDFORM_FIELD_EDIT_OPTIONS')?></legend>
	
		<table class="adminlist">
			<thead>
				<tr>
					<th><?php echo JText::_('VALUE'); ?></th>
					<th><?php echo JText::_('COM_REDFORM_FIELD_LABEL_LABEL'); ?></th>
					<th><?php echo JText::_('PRICE'); ?></th>
					<th><?php echo JText::_('PUBLISHED'); ?></th>
					<th><?php echo JText::_('ORDERING'); ?></th>
		      <th>&nbsp;</th>
		      <th>&nbsp;</th>
				</tr>
			</thead>
		
			<tbody>
				<tr>
					<td colspan="7">
						<a href="<?php echo JRoute::_('index.php?option=com_redform&controller=values&task=ajaxedit&tmpl=component&fieldid=' .$this->row->id); ?>" class="valuemodal">
							<?php echo JText::_('Add'); ?>
						</a>
					</td>
				</tr>
			</tbody>
		
			<tbody id="values-rows">
				<tr>
					<td colspan="7">
					</td>
				</tr>
			</tbody>
		</table>
				
		</fieldset>
		<!-- Values table end-->
				
		<?php if ($this->parameters && $this->parameters->getGroups()): ?>		
		<?php
			foreach ( $this->parameters->getGroups() as $key => $groups )
			{
				$gname = ( strtolower($key) == '_default' ) ? JText::_( 'Extra' ) : $key;
				?>
				<fieldset class="adminform">
					<legend>
						<?php
						echo JText::_( $gname );
						?>
					</legend>
					<?php
					// render is defined in joomla\libraries\joomla\html\parameter.php
					echo $this->parameters->render( 'params', $key );
					?>
				</fieldset>
				<?php
			}
		?>
		<?php endif; ?>
		
  <?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="id" id="fieldid" value="<?php echo $this->row->id; ?>" />
	<?php if ($this->state == 'disabled') { ?><input type="hidden" name="form_id" value="<?php echo $this->form_id; ?>" /><?php } ?>
	<input type="hidden" name="ordering" value="<?php echo $this->row->ordering; ?>" />
	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="fields" />
</form>