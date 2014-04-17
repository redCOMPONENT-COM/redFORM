<?php
/**
 * @package     Redform.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

// HTML helpers
JHtml::_('behavior.keepalive');
JHTML::_('behavior.formvalidation');
JHtml::_('rbootstrap.tooltip');
JHtml::_('rjquery.chosen', 'select');
JHtml::_('rsearchtools.main');

$action = 'index.php?option=com_redform&task=field.edit&id=' . $this->item->id;
$input = JFactory::getApplication()->input;
$tab = $input->getString('tab', 'details');
$isNew = (int) $this->item->id <= 0;

$tableSortLink = 'index.php?option=com_redform&task=values.saveOrderAjax&tmpl=component';
JHTML::_('rsortablelist.sortable', 'valuesTable', 'adminForm', 'asc', $tableSortLink, true, true);
?>
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton) {

		var form = document.adminForm;
		if (pressbutton == 'field.cancel') {
			submitform(pressbutton);
			return true;
		}
		if (document.formvalidator.isValid(form)) {
			submitform(pressbutton);
			return true;
		}
		else {
			return false;
		}
	}
</script>
<?php if ($this->item->id) : ?>
	<script type="text/javascript">
		(function ($) {

			var redformvalues = new Class({

				initialize : function() {
					$('.save-option').click(function(event){
						var parents = $(event.currentTarget).parents('tr');
						var elements = $(parents[0]).find(':input');
						$.ajax({
							url: 'index.php?option=com_redform&task=field.saveOption&format=json&view=field&id=<?php echo $this->item->id ?>',
							beforeSend: function (xhr) {
								$('.values-content-content .spinner').show();
							}
							,
							data:elements.serialize(),
							type : 'POST'
						}).done(function (data) {
							$('.values-content-content .spinner').hide();
						});
					});
				},

				getValues : function() {
					var that = this;

					// Perform the ajax request
					$.ajax({
						url: 'index.php?option=com_redform&task=field.getValues&format=json&view=field&id=<?php echo $this->item->id ?>',
						dataType: 'json',
						beforeSend: function (xhr) {
							$('.values-content-content .spinner').show();
						}
					}).done(function(data) {
						$('.values-content-content .spinner').hide();

						if (data && data.length) {
							for (var i = 0; i < data.length; i++) {
								that.addOption(data[i]);
							}
						}
					});
				},

				addOption : function(data) {
					var tr = $('#newvalue').clone().removeAttr('id');
					tr.find('[name^=option-id]').val(data.id);
					tr.find('[name^=option-value]').val(data.value);
					tr.find('[name^=option-label]').val(data.label);
					tr.find('[name^=option-price]').val(data.price);
					tr.find('[name^=order]').val(data.ordering);
					tr.find('td.buttons .save-option').text('save').removeClass('btn-success').addClass('btn-primary');

					var btnremove = $('<button/>', {
						'type' : 'button',
						'class': 'btn btn-danger btn-sm',
						'optionId': data.id
					}).click(function(event){
						var element = $(event.currentTarget);
						$.ajax({
							url: 'index.php?option=com_redform&task=field.removeValue&format=json&view=field&id=<?php echo $this->item->id ?>',
							data: {'optionId' : element.attr('optionId')},
							type : 'POST',
							dataType: 'json',
							beforeSend: function (xhr) {
								$('.values-content-content .spinner').show();
							}
						}).done(function(data) {
							$('.values-content-content .spinner').hide();

							if (data && data.success) {
								var parents = element.parents('tr');
								$(parents[0]).remove();
							}
						});
					}).text('delete');
					tr.find('td.buttons .save-option').after(btnremove);

					$('#newvalue').before(tr);
				}
			});

			$(document).ready(function () {
				if ($('#tabvalues')) {
					var obj = new redformvalues();
					obj.getValues();
				}
			});
		})(jQuery);
	</script>
	<?php if ($tab) : ?>
		<script type="text/javascript">
			jQuery(document).ready(function () {
				// Show the corresponding tab
				jQuery('#fieldTabs a[href="#<?php echo $tab ?>"]').tab('show');
			});
		</script>
	<?php endif; ?>
<?php endif; ?>

<ul class="nav nav-tabs" id="fieldTabs">
	<li class="active">
		<a href="#details" data-toggle="tab">
			<?php echo JText::_('COM_REDFORM_DETAILS'); ?>
		</a>
	</li>

	<?php if ($this->item->hasOptions && $this->item->id) : ?>
		<li>
			<a href="#values" data-toggle="tab">
				<?php echo JText::_('COM_REDFORM_FIELD_TAB_OPTIONS'); ?>
			</a>
		</li>
	<?php endif; ?>
</ul>
<div class="tab-content">
	<div class="tab-pane active" id="details">
		<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
		      class="form-validate form-horizontal">
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('field'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('field'); ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('field_header'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('field_header'); ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('form_id'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('form_id'); ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('fieldtype'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('fieldtype'); ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('tooltip'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('tooltip'); ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('validate'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('validate'); ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('unique'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('unique'); ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('readonly'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('readonly'); ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('default'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('default'); ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('published'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('published'); ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('redmember_field'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('redmember_field'); ?>
				</div>
			</div>

			<?php foreach ($this->form->getGroup('params') as $field) : ?>
				<div class="control-group">
					<?php if ($field->type == 'Spacer') : ?>
						<?php echo $field->label; ?>
					<?php else : ?>
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
						<div class="controls">
							<?php echo $field->input; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>

			<!-- hidden fields -->
			<?php echo $this->form->getInput('id'); ?>
			<input type="hidden" name="task" value="">
			<?php echo JHTML::_('form.token'); ?>
		</form>
	</div>
	<?php if ($this->item->hasOptions && $this->item->id) : ?>
		<div class="tab-pane" id="values">
			<div class="row-fluid values-content">
				<table class="table table-striped table-hover" id="valuesTable">
					<thead>
					<tr>
						<th width="1%">
							&nbsp;
						</th>
						<th class="nowrap center">
							<?php echo JText::_('COM_REDFORM_FIELD_VALUES_TABLE_HEADER_VALUE'); ?>
						</th>
						<th class="nowrap center">
							<?php echo JText::_('COM_REDFORM_FIELD_VALUES_TABLE_HEADER_LABEL'); ?>
						</th>
						<th class="nowrap center">
							<?php echo JText::_('COM_REDFORM_FIELD_VALUES_TABLE_HEADER_PRICE'); ?>
						</th>
						<th class="nowrap center">
							&nbsp;
						</th>
					</tr>
					</thead>
					<tbody>
						<tr id="newvalue">
							<td class="order nowrap center">
								<span class="sortable-handler hasTooltip inactive">
									<i class="icon-move"></i>
								</span>
								<input type="text" style="display:none" name="order[]" value="0" class="text-area-order" />
							</td>
							<td>
								<input type="text" name="option-value[]" placeholder="<?php echo JText::_('COM_REDFORM_FIELD_VALUES_TABLE_ENTER_VALUE'); ?>"/>
							</td>
							<td>
								<input type="text" name="option-label[]" placeholder="<?php echo JText::_('COM_REDFORM_FIELD_VALUES_TABLE_ENTER_LABEL'); ?>"/>
							</td>
							<td>
								<input type="text" name="option-price[]" placeholder="<?php echo JText::_('COM_REDFORM_FIELD_VALUES_TABLE_ENTER_PRICE'); ?>"/>
							</td>
							<td class="buttons">
								<input type="hidden" name="option-id[]" value="" />
								<button type="button" name="option-save-button[]" class="save-option btn btn-success btn-sm"><?php echo JText::_('COM_REDFORM_FIELD_VALUES_TABLE_ADD'); ?></button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	<?php endif; ?>
</div>
