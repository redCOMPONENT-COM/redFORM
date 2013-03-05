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
		document.id('fieldtype').addEvent('click', function(){
			if (document.id('form_id').value == 0) {
				alert("<?php echo JText::_('COM_REDFORM_FIELD_JS_PLEASE_SELECT_FORM_FIRST'); ?>");
			}
		});

		document.id('fieldtype').addEvent('change', function(){
			if (confirm("<?php echo JText::_('COM_REDFORM_FIELD_JS_CONFIRM_CHANGE_TYPE'); ?>")) {
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
	var edittext = "<?php echo JText::_('COM_REDFORM_COM_REDEVENT_EDIT'); ?>";
	var deletetext = "<?php echo JText::_('COM_REDFORM_COM_REDEVENT_DELETE'); ?>";
	var textyes = "<?php echo JText::_('JYES'); ?>";
	var textno = "<?php echo JText::_('JNO'); ?>";
	var textup = "<?php echo JText::_('COM_REDFORM_UP'); ?>";
	var textdown = "<?php echo JText::_('COM_REDFORM_DOWN'); ?>";

	function addMailingListField()
	{
		var newrow = $$('.listname-row')[0].clone();
		newrow.getElement('input[name^=listname]').value = '';
		newrow.getElement('.listname-delete').addEvent('click', removeMailingListField);
		newrow.inject(document.id('mailinglist-table'));
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
			alert( "<?php echo JText::_('COM_REDFORM_FIELD_NAME_REQUIRED'); ?>" );
		} else {
			submitform( pressbutton );
		}
	}
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">

	<div class="span8 form-horizontal">
	<fieldset>
		<div class="control-group">
			<div class="control-label">
				<label for="field"><?php echo JHTML::tooltip(JText::_('COM_REDFORM_FIELD_NAME_TIP'), JText::_('COM_REDFORM_FIELD_NAME_TIP'), 'tooltip.png', '', '', false); ?>
				<?php echo JText::_('COM_REDFORM_Field'); ?></label>
			</div>
			<div class="controls">
				<input class="inputbox" type="text" size="80" name="field" value="<?php echo $this->row->field; ?>"/>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<label for="field_header"><?php echo JHTML::tooltip(JText::_('COM_REDFORM_COM_REDEVENT_FIELD_FORM_FIELD_HEADER_DESC'), JText::_('COM_REDFORM_COM_REDEVENT_FIELD_FORM_FIELD_HEADER_LABEL'), 'tooltip.png', '', '', false); ?>
				<?php echo JText::_('COM_REDFORM_COM_REDEVENT_FIELD_FORM_FIELD_HEADER_LABEL'); ?></label>
			</div>
			<div class="controls">
				<input class="inputbox" type="text" size="80" name="field_header" value="<?php echo $this->row->field_header; ?>"/>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<label for="form"><?php echo JHTML::tooltip(JText::_('COM_REDFORM_COM_REDEVENT_FIELD_SELECT_FORM_DESC'), JText::_('COM_REDFORM_Form'), 'tooltip.png', '', '', false); ?>
				<?php echo JText::_('COM_REDFORM_Form'); ?></label>
			</div>
			<div class="controls">
				<?php echo $this->lists['forms']; ?>
			</div>
		</div>

		<div class="control-group" id="trfieldtypes">
			<div class="control-label">
				<label for="fieldtype"><?php echo JText::_('COM_REDFORM_FIELD_TYPE'); ?></label>
			</div>
			<div class="controls" id="newfieldtype">
				<?php echo $this->lists['fieldtypes']; ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<label for="tooltip"><?php echo JHTML::tooltip(JText::_('COM_REDFORM_ENTER_TOOLTIP_INFO'), JText::_('COM_REDFORM_ENTER_TOOLTIP'), 'tooltip.png', '', '', false); ?>
				<?php echo JText::_('COM_REDFORM_ENTER_TOOLTIP'); ?></label>
			</div>
			<div class="controls">
				<textarea name="tooltip" cols="80" rows="5"><?php echo $this->row->tooltip; ?></textarea>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<label for="required"><?php echo JHTML::tooltip(JText::_('COM_REDFORM_FIELD_REQUIRED_TIP'), JText::_('COM_REDFORM_Required'), 'tooltip.png', '', '', false); ?>
				<?php echo JText::_('COM_REDFORM_Required'); ?></label>
			</div>
			<div class="controls">
				<?php echo $this->lists['validate']; ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<label for="unique"><?php echo JHTML::tooltip(JText::_('COM_REDFORM_FIELD_UNIQUE_TIP'), JText::_('COM_REDFORM_Unique'), 'tooltip.png', '', '', false); ?>
				<?php echo JText::_('COM_REDFORM_Unique'); ?></label>
			</div>
			<div class="controls">
				<?php echo $this->lists['unique']; ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
			<label for="readonly"><?php echo JHTML::tooltip(JText::_('COM_REDFORM_FIELD_READONLY_TIP'), JText::_('COM_REDFORM_FIELD_READONLY'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('COM_REDFORM_FIELD_READONLY'); ?></label>
			</div>
			<div class="controls">
			<?php echo JHTML::_('select.booleanlist', 'readonly', '', $this->row->readonly); ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
			<label for="default"><?php echo JHTML::tooltip(JText::_('COM_REDFORM_FIELD_DEFAULT_VALUE_TIP'), JText::_('COM_REDFORM_FIELD_DEFAULT_VALUE'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('COM_REDFORM_FIELD_DEFAULT_VALUE'); ?></label>
			</div>
			<div class="controls">
			<textarea name="default" cols="80" rows="2"><?php echo $this->row->default; ?></textarea>
			</div>
		</div>

		<?php if (REDMEMBER_INTEGRATION): ?>
		<div class="control-group">
			<div class="control-label">
			<label for="redmember_field"><?php echo JHTML::tooltip(JText::_('COM_REDFORM_SELECT_REDMEMBER_FIELD'), JText::_('COM_REDFORM_REDMEMBER_FIELD'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('COM_REDFORM_Redmember_field'); ?></label>
			</div>
			<div class="controls">
			<?php echo $this->lists['rmfields']; ?>
			</div>
		</div>
		<?php endif; ?>

		<div class="control-group">
			<div class="control-label">
			<label for="published"><?php echo JHTML::tooltip(JText::_('COM_REDFORM_FIELD_PUBLISHED_TIP'), JText::_('COM_REDFORM_Published'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('COM_REDFORM_Published'); ?></label>
			</div>
			<div class="controls">
			<?php echo $this->lists['published']; ?>
			</div>
		</div>
	</fieldset>

	<?php if (isset($this->displaymailinglist) && $this->displaymailinglist):
		$listnames = explode(';', $this->mailinglist->listnames);
		?>
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_REDFORM_FIELD_EDIT_MAILINGLIST_FIELDSET')?></legend>

		<table class="admintable" id="mailinglist-table">
		<tr id="trmailinglist">
			<td class="key hasTip" title="<?php echo JText::_('COM_REDFORM_NEWSLETTERS').'::'.JText::_('COM_REDFORM_NEWSLETTERS_TIP'); ?>">
				<?php echo JText::_('COM_REDFORM_NEWSLETTERS'); ?>
			</td>
			<td>
				<div id="newmailinglist">
					<?php echo $this->lists['mailinglists']; ?>
				</div>
			</td>
		</tr>
		<tr id="traddlists">
			<td class="key hasTip" title="<?php echo JText::_('COM_REDFORM_ADD_LISTS').'::'.JText::_('COM_REDFORM_ADD_LISTS_TIP'); ?>">
				<?php echo JText::_('COM_REDFORM_ADD_LISTS'); ?>
			</td>
			<td>
				<a href="#" onClick="addMailingListField(); return false;"><?php echo JText::_('COM_REDFORM_ADD_LIST'); ?></a>
			</td>
		</tr>
		<?php
			foreach ($listnames as $key => $name): ?>
				<tr class="listname-row">
					<td class="key hasTip" title="<?php echo JText::_('COM_REDFORM_LISTNAME').'::'.JText::_('COM_REDFORM_LISTNAME_TIP'); ?>">
						<?php echo JText::_('COM_REDFORM_LISTNAME'); ?>
					</td>
					<td>
						<input type="text" name="listname[]" value="<?php echo $name; ?>"/>&nbsp;
						<a href="#" class="listname-delete"><?php echo JText::_('COM_REDFORM_REMOVE_LIST'); ?></a>
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
					<th><?php echo JText::_('COM_REDFORM_VALUE'); ?></th>
					<th><?php echo JText::_('COM_REDFORM_FIELD_LABEL_LABEL'); ?></th>
					<th><?php echo JText::_('COM_REDFORM_PRICE'); ?></th>
					<th><?php echo JText::_('COM_REDFORM_PUBLISHED'); ?></th>
					<th><?php echo JText::_('COM_REDFORM_ORDERING'); ?></th>
		      <th>&nbsp;</th>
		      <th>&nbsp;</th>
				</tr>
			</thead>

			<tbody>
				<tr>
					<td colspan="7">
						<a href="<?php echo JRoute::_('index.php?option=com_redform&controller=values&task=ajaxedit&tmpl=component&fieldid=' .$this->row->id); ?>"
						   class="modal btn"
						   rel="{handler: 'iframe', size: {x: 800, y: 500}}"
						   >
							<?php echo JText::_('COM_REDFORM_Add'); ?>
						</a>
					</td>
				</tr>
			</tbody>

			<tbody id="values-rows">
			</tbody>
		</table>

		</fieldset>
		<!-- Values table end-->
	</div>

	<?php if ($this->row->form): ?>
	<div class="span4">
    <?php
    // Iterate through the normal form fieldsets and display each one.
    foreach ($this->row->form->getFieldsets('params') as $fieldsets => $fieldset):
    ?>
    <fieldset class="form-vertical">
        <legend>
            <?php echo JText::_('COM_REDFORM_FIELDSET_LABEL_'.$fieldset->name); ?>
        </legend>
			<?php
			// Iterate through the fields and display them.
			foreach($this->row->form->getFieldset($fieldset->name) as $field):
			    // If the field is hidden, only use the input.
			    if ($field->hidden):
			        echo $field->input;
			    else:
			    ?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $field->label; ?>
					</div>
					<div class="controls">
						<?php echo $field->input; ?>
					</div>
				</div>
			    <?php
			    endif;
			endforeach;
			?>
    </fieldset>
    <?php
    endforeach;
    ?>
</div>
		<?php endif; ?>

  <?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="id" id="fieldid" value="<?php echo $this->row->id; ?>" />
	<?php if ($this->state == 'disabled') { ?><input type="hidden" name="form_id" value="<?php echo $this->form_id; ?>" /><?php } ?>
	<input type="hidden" name="ordering" value="<?php echo $this->row->ordering; ?>" />
	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="fields" />
</form>