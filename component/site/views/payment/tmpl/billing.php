<?php
/**
 * @package    Redform.Site
 *
 * @copyright  Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.formvalidation');
?>
<h2 class="redform-billto"><?php echo JText::_('COM_REDFORM_BILLING_TITLE');?></h2>
<form action="<?php echo $this->action; ?>" method="post" class="form-validate form-horizontal">

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->billingform->getLabel('fullname'); ?>
		</div>
		<div class="controls">
			<?php echo $this->billingform->getInput('fullname'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->billingform->getLabel('company'); ?>
		</div>
		<div class="controls">
			<?php echo $this->billingform->getInput('company'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->billingform->getLabel('iscompany'); ?>
		</div>
		<div class="controls">
			<?php echo $this->billingform->getInput('iscompany'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->billingform->getLabel('vatnumber'); ?>
		</div>
		<div class="controls">
			<?php echo $this->billingform->getInput('vatnumber'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->billingform->getLabel('address'); ?>
		</div>
		<div class="controls">
			<?php echo $this->billingform->getInput('address'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->billingform->getLabel('city'); ?>
		</div>
		<div class="controls">
			<?php echo $this->billingform->getInput('city'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->billingform->getLabel('zipcode'); ?>
		</div>
		<div class="controls">
			<?php echo $this->billingform->getInput('zipcode'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->billingform->getLabel('phone'); ?>
		</div>
		<div class="controls">
			<?php echo $this->billingform->getInput('phone'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->billingform->getLabel('email'); ?>
		</div>
		<div class="controls">
			<?php echo $this->billingform->getInput('email'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->billingform->getLabel('country'); ?>
		</div>
		<div class="controls">
			<?php echo $this->billingform->getInput('country'); ?>
		</div>
	</div>

	<div class="control-group total">
		<div class="control-label">
			<?php echo JText::_('COM_REDFORM_Total'); ?>
		</div>
		<div class="controls">
			<?php echo $this->currency . ' ' . $this->price; ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<label for="gw"><?php echo JText::_('COM_REDFORM_Select_payment_method'); ?></label>
		</div>
		<div class="controls">
			<?php echo $this->lists['gwselect']; ?>
		</div>
	</div>

	<input type="submit" value="<?php echo JText::_('COM_REDFORM_Continue'); ?>"/>

	<input type="hidden" name="task" value="payment.process"/>
	<input type="hidden" name="reference" value="<?php echo $this->reference; ?>"/>
	<input type="hidden" name="source" value="<?php echo $this->source; ?>"/>
</form>
