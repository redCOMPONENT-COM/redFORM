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
JHtml::_('rjquery.chosen', 'select');
JHtml::_('rsearchtools.main');

$action = JRoute::_('index.php?option=com_redform&view=field');
$input = JFactory::getApplication()->input;
$tab = $input->getString('tab', 'details');
$isNew = (int) $this->item->id <= 0;
?>
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
					<?php echo $this->form->getLabel('parent_id'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('parent_id'); ?>
				</div>
			</div>

			<!-- hidden fields -->
			<input type="hidden" name="option" value="com_redform">
			<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
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
