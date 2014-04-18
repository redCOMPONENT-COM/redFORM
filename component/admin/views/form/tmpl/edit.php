<?php
/**
 * @package     Redform.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

// HTML helpers
JHtml::_('behavior.keepalive');
JHtml::_('rbootstrap.tooltip');
JHtml::_('rjquery.chosen', 'select');
JHtml::_('rsearchtools.main');

$action = JRoute::_('index.php?option=com_redform&view=form');
$input = JFactory::getApplication()->input;
$tab = $input->getString('tab');
$isNew = (int) $this->item->id <= 0;

?>
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
		<a href="#payment" data-toggle="tab">
			<?php echo JText::_('COM_REDFORM_PAYMENT'); ?>
		</a>
	</li>
</ul>

<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
      class="form-validate form-horizontal">
	<div class="tab-content">
		<div class="tab-pane active" id="details">
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
						<?php echo $this->form->getLabel('captchaactive'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('captchaactive'); ?>
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
						<?php echo $this->form->getInput('notificationtext'); ?>
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
						<?php echo $this->form->getLabel('contactpersonemail'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('contactpersonemail'); ?>
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
					<?php echo $this->form->getLabel('show_js_price'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('show_js_price'); ?>
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
					<?php echo $this->form->getLabel('paymentprocessing'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('paymentprocessing'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('paymentaccepted'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('paymentaccepted'); ?>
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
					<?php echo $this->form->getInput('submitterpaymentnotificationbody'); ?>
				</div>
			</div>
		</div>
	</div>

	<!-- hidden fields -->
	<input type="hidden" name="option" value="com_redform">
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
	<input type="hidden" name="task" value="">
	<?php echo JHTML::_('form.token'); ?>
</form>
