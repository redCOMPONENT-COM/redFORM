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
JHtml::_('rbootstrap.tooltip');
JHtml::_('rjquery.select2', 'select');
JHTML::_('behavior.formvalidation');

$action = 'index.php?option=com_redform&task=field.edit&id=' . $this->item->id;
$input = JFactory::getApplication()->input;
$tab = $input->getString('tab', 'details');
$isNew = (int) $this->item->id <= 0;

$firstSpacer = false;
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
		var loadedFieldTabs = {};
		(function ($) {
			function ajaxFieldTabSetup(tabName) {
				$('a[href="#' + tabName + '"]').on('shown', function (e) {

					// Tab already loaded
					if (loadedFieldTabs[tabName] == true) {
						return true;
					}

					// Perform the ajax request
					$.ajax({
						url: 'index.php?option=com_redform&task=field.ajax' + tabName + '&view=field&id=<?php echo $this->item->id ?>',
						beforeSend: function (xhr) {
							$('.' + tabName + '-content .spinner').show();
							$('#fieldTabs').addClass('opacity-40');
						}
					}).done(function (data) {
							$('.' + tabName + '-content .spinner').hide();
							$('#fieldTabs').removeClass('opacity-40');
							$('.' + tabName + '-content').html(data);
							$('select').chosen();
							$('.chzn-search').hide();
							$('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
								"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});
							loadedFieldTabs[tabName] = true;

							// Auto submit search fields after loading AJAX
							$('.js-enter-submits').enterSubmits();
						});
				})
			}

			$(document).ready(function () {
				ajaxFieldTabSetup('extra');
				ajaxFieldTabSetup('values');
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

	<?php if ($this->item->id) : ?>
		<li>
			<a href="#values" data-toggle="tab">
				<?php echo JText::_('COM_REDFORM_FIELD_VALUES_LIST'); ?>
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
					<?php if ($field->type == 'Spacerr') : ?>
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
	<?php if ($this->item->id) : ?>
		<div class="tab-pane" id="values">
			<div class="row-fluid values-content">
				<div class="spinner pagination-centered">
					<?php echo JHtml::image('com_redform/ajax-loader.gif', '', null, true); ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>
