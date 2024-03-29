<?php
/**
 * @package     Redform.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

// HTML helpers
JHtml::_('behavior.keepalive');
JHtml::_('rbootstrap.tooltip', '.hasToolTip');
JHtml::_('rjquery.chosen', 'select');
JHtml::_('rsearchtools.main');
JHTML::_('behavior.formvalidation');

$action = JRoute::_('index.php?option=com_redform&view=form');
$input = JFactory::getApplication()->input;
$tab = $input->getString('tab');
$isNew = (int) $this->item->id <= 0;
$showEmailNotice = false;

/** @var RForm $form */
$form = $this->form;

if (!$isNew)
{
	$formEntity = RdfEntityForm::load($this->item->id);
	$showEmailNotice = !$formEntity->hasEmailContactField();
}
?>
<?php if ($this->item->id) :
	$tableSortLink = 'index.php?option=com_redform&task=formfields.saveOrderAjax&tmpl=component';
	JHTML::_('rsortablelist.main');
	?>
	<script type="text/javascript">
		Joomla.submitbutton = function(pressbutton) {
			var form = document.adminForm;

			if (pressbutton == 'form.cancel') {
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
		};

		(function ($) {
			$(document).ready(function () {
				$('#jform_params_auto_delete').change(function() {
					if ($(this).val() == -1) {
					    $('#auto_delete_custom_group').show();
					}
					else {
					    $('#auto_delete_custom_group').hide();
					}
				}).trigger('change');

				// Perform the ajax request for fields in tab
				$.ajax({
					url: 'index.php?option=com_redform&task=form.ajaxfields&view=form&id=<?php echo $this->item->id ?>',
					cache: false,
					beforeSend: function (xhr) {
						$('.fields-content .spinner').show();
					}
				}).done(function (data) {
					$('.fields-content .spinner').hide();
					$('.fields-content').html(data);
					$('select').chosen();
					$('.chzn-search').hide();
					$('.hasTooltip').tooltip({"animation": true, "html": true, "placement": "top",
						"selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false});

					// Auto submit search fields after loading AJAX
					$('.fields-content .js-enter-submits').keydown(function(event) {
						// Key is enter?
						if (event.which === 13) {
							event.preventDefault();
							$(this).closest("form").submit();
						}
					});

					var sortableList = new $.JSortableList('#fieldList tbody','fieldsForm','asc' , '<?php echo $tableSortLink; ?>','','false');
				});
			});
		})(jQuery);
	</script>
	<?php if ($tab) : ?>
		<script type="text/javascript">
			jQuery(document).ready(function () {

				// Show the corresponding tab
				jQuery('#formTabs a[href="#<?php echo $tab ?>"]').tab('show');
			});
		</script>
	<?php endif; ?>
<?php endif; ?>

<ul class="nav nav-tabs" id="formTabs">
	<li class="active">
		<a href="#details" data-toggle="tab">
			<?php echo JText::_('COM_REDFORM_DETAILS'); ?>
		</a>
	</li>
	<li>
		<a href="#notification" data-toggle="tab">
			<?php echo JText::_('COM_REDFORM_NOTIFICATION'); ?>
		</a>
	</li>
	<li>
		<a href="#confirmation" data-toggle="tab">
			<?php echo JText::_('COM_REDFORM_FORM_TAB_CONFIRMATION'); ?>
		</a>
	</li>
	<li>
		<a href="#payment" data-toggle="tab">
			<?php echo JText::_('COM_REDFORM_PAYMENT'); ?>
		</a>
	</li>

	<?php if ($this->item->id) : ?>
	<li>
		<a href="#fields" data-toggle="tab">
			<?php echo JText::_('COM_REDFORM_FIELDS'); ?>
		</a>
	</li>
	<?php endif;
	foreach ($form->getFieldsets('params') as $fieldset):
		if (in_array($fieldset->name, ['extra', 'gdpr']))
		{
			continue;
		}
		?>
		<li>
			<a href="#<?php echo $fieldset->name; ?>" data-toggle="tab">
				<?php echo $fieldset->label ?: $fieldset->name; ?>
			</a>
		</li>
		<?php endforeach; ?>
</ul>

<?php if ($showEmailNotice): ?>
	<div class="alert alert-warning alert-dismissible" role="alert">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<?php echo Text::_('COM_REDFORM_FORM_MISSING_EMAIL_CONTACT_FIELD'); ?>
	</div>
<?php endif; ?>
<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
      class="form-validate form-horizontal">
<div class="tab-content">
		<div class="tab-pane active" id="details">
			<div class="row-fluid">
				<div class="span6">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('formname'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('formname'); ?>
						</div>
					</div>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('showname'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('showname'); ?>
						</div>
					</div>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('contactpersonemail'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('contactpersonemail'); ?>
						</div>
					</div>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('captchaactive'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('captchaactive'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('allow_frontend_edit', 'params'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('allow_frontend_edit', 'params'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('submit_label', 'params'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('submit_label', 'params'); ?>
						</div>
					</div>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('ajax_submission', 'params'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('ajax_submission', 'params'); ?>
						</div>
					</div>
				</div>

				<div class="span6">

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('access'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('access'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('classname'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('classname'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('startdate'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('startdate'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('formexpires'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('formexpires'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('enddate'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('enddate'); ?>
						</div>
					</div>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('auto_delete', 'params'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('auto_delete', 'params'); ?>
						</div>
					</div>

					<div class="control-group" id="auto_delete_custom_group">
						<div class="control-label">
							<?php echo $this->form->getLabel('auto_delete_custom', 'params'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('auto_delete_custom', 'params'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="tab-pane" id="notification">
			<div class="row-fluid notification-content">
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('submitnotification'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('submitnotification'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('notificationtext'); ?>
					</div>
					<div class="controls">
						<div class="tags-info"><?php echo $this->form->getInput('notificationtext_tags'); ?></div>
						<?php echo $this->form->getInput('notificationtext'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('notification_extra', 'params'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('notification_extra', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('redirect'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('redirect'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('contactpersoninform'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('contactpersoninform'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('enable_admin_notification', 'params'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('enable_admin_notification', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('admin_notification_email_mode'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('admin_notification_email_mode'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('admin_notification_email_subject'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('admin_notification_email_subject'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('admin_notification_email_body'); ?>
					</div>
					<div class="controls">
						<div class="tags-info"><?php echo $this->form->getInput('admin_notification_email_body_tags'); ?></div>
						<?php echo $this->form->getInput('admin_notification_email_body'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('contactpersonfullpost'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('contactpersonfullpost'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('submitterinform'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('submitterinform'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('submissionsubject'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('submissionsubject'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('submissionbody'); ?>
					</div>
					<div class="controls">
						<div class="tags-info"><?php echo $this->form->getInput('submissionbody_tags'); ?></div>
						<?php echo $this->form->getInput('submissionbody'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('cond_recipients'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('cond_recipients'); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="tab-pane" id="confirmation">
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('enable_confirmation'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('enable_confirmation'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('enable_confirmation_notification'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('enable_confirmation_notification'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('confirmation_notification_recipients'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('confirmation_notification_recipients'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('confirmation_contactperson_subject'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('confirmation_contactperson_subject'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('confirmation_contactperson_body'); ?>
				</div>
				<div class="controls">
					<div class="tags-info"><?php echo $this->form->getInput('confirmation_contactperson_body_tags'); ?></div>
					<?php echo $this->form->getInput('confirmation_contactperson_body'); ?>
				</div>
			</div>
		</div>

		<div class="tab-pane" id="payment">
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('activatepayment'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('activatepayment'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('currency'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('currency'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('requirebilling'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('requirebilling'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('enable_contact_payment_notification', 'params'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('enable_contact_payment_notification', 'params'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('paymentprocessing'); ?>
				</div>
				<div class="controls">
					<div class="tags-info"><?php echo $this->form->getInput('paymentprocessing_tags'); ?></div>
					<?php echo $this->form->getInput('paymentprocessing'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('paymentaccepted'); ?>
				</div>
				<div class="controls">
					<div class="tags-info"><?php echo $this->form->getInput('paymentaccepted_tags'); ?></div>
					<?php echo $this->form->getInput('paymentaccepted'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('paymentfailed', 'params'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('paymentfailed', 'params'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('contactpaymentnotificationsubject'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('contactpaymentnotificationsubject'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('contactpaymentnotificationbody'); ?>
				</div>
				<div class="controls">
					<div class="tags-info"><?php echo $this->form->getInput('contactpaymentnotificationbody_tags'); ?></div>
					<?php echo $this->form->getInput('contactpaymentnotificationbody'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('submitterpaymentnotificationsubject'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('submitterpaymentnotificationsubject'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('submitterpaymentnotificationbody'); ?>
				</div>
				<div class="controls">
					<div class="tags-info"><?php echo $this->form->getInput('submitterpaymentnotificationbody_tags'); ?></div>
					<?php echo $this->form->getInput('submitterpaymentnotificationbody'); ?>
				</div>
			</div>
		</div>

	<?php
	foreach ($form->getFieldsets('params') as $fieldset):
		if (in_array($fieldset->name, ['extra', 'gdpr']))
		{
			continue;
		}
	?>
	<div class="tab-pane" id="<?php echo $fieldset->name ?>">
		<?php foreach ($form->getFieldset($fieldset->name) as $field): ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $field->label ?>
			</div>
			<div class="controls">
				<?php echo $field->input ?>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endforeach; ?>

		<!-- hidden fields -->
		<input type="hidden" name="option" value="com_redform">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="task" value="">
		<?php echo JHTML::_('form.token'); ?>

	<?php if ($this->item->id) : ?>
		<div id="fields" class="tab-pane">
			<div class="container-fluid">
				<div class="row-fluid fields-content">
					<div class="spinner pagination-centered">
						<?php echo JHtml::image('media/com_redform/images/ajax-loader.gif', '') ?>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>

</form>
