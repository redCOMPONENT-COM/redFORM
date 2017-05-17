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

if ($this->item->hasOptions && $this->item->id)
{
	JText::script('COM_REDFORM_JS_FIELD_VALUES_SAVE');
	JText::script('COM_REDFORM_JS_FIELD_VALUES_DELETE');
	RHelperAsset::load('field-values.js', 'com_redform');

	$tableSortLink = 'index.php?option=com_redform&task=values.saveOrderAjax&tmpl=component';
	JHTML::_('rsortablelist.sortable', 'valuesTable', 'optionsForm', 'asc', $tableSortLink, true, true);
}
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

<?php if ($tab) : ?>
	<script type="text/javascript">
		jQuery(document).ready(function () {
			// Show the corresponding tab
			jQuery('#fieldTabs a[href="#<?php echo $tab ?>"]').tab('show');
		});
	</script>
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

			<?php if (!$this->item->id): ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('form_id'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('form_id'); ?>
				</div>
			</div>
			<?php endif; ?>

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
					<?php echo $this->form->getLabel('default'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('default'); ?>
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

			<?php foreach ($this->form->getFieldsets('params') as $fieldset) : ?>
				<?php foreach ($this->form->getFieldset($fieldset->name) as $field) : ?>
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
				<form name="optionsForm" id="optionsForm">
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
							<?php echo JText::_('COM_REDFORM_FIELD_VALUES_TABLE_HEADER_SKU'); ?>
						</th>
						<th class="nowrap center">
							&nbsp;
						</th>
					</tr>
					</thead>
					<tbody>
						<tr id="newvalue">
							<td class="order nowrap center">
								<span class="sortable-handler hasTooltip hide">
									<i class="icon-move"></i>
								</span>
								<input type="checkbox" name="option-id[]" value="" checked="checked" class="hide"/>
								<input type="text" style="display:none" name="order-fake[]" value="0" class="text-area-order" />
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
							<td>
								<input type="text" name="option-sku[]" placeholder="<?php echo JText::_('COM_REDFORM_FIELD_VALUES_TABLE_ENTER_SKU'); ?>"/>
							</td>
							<td class="buttons">
								<button type="button" name="option-save-button[]" class="save-option btn btn-success btn-sm"><?php echo JText::_('COM_REDFORM_FIELD_VALUES_TABLE_ADD'); ?></button>
							</td>
						</tr>
					</tbody>
				</table>
				</form>
			</div>
		</div>
	<?php endif; ?>
</div>
