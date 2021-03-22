<?php
/**
 * @package     Redform.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\Language\Text;

$action = JRoute::_('index.php?option=com_redform&view=cart');

/**
 * @var RdfEntityCart
 */
$cart = $this->cart;
?>
<form action="<?php echo $action; ?>" method="post" class="form-validate form-horizontal" id="billingForm">
	<div class="cart__billing">
		<h3><?php echo JText::_('COM_REDFORM_BILLING_INFO_TITLE'); ?></h3>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->billingForm->getLabel('fullname'); ?>
			</div>
			<div class="controls">
				<?php echo $this->billingForm->getInput('fullname'); ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->billingForm->getLabel('company'); ?>
			</div>
			<div class="controls">
				<?php echo $this->billingForm->getInput('company'); ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->billingForm->getLabel('iscompany'); ?>
			</div>
			<div class="controls">
				<?php echo $this->billingForm->getInput('iscompany'); ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->billingForm->getLabel('vatnumber'); ?>
			</div>
			<div class="controls">
				<?php echo $this->billingForm->getInput('vatnumber'); ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->billingForm->getLabel('address'); ?>
			</div>
			<div class="controls">
				<?php echo $this->billingForm->getInput('address'); ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->billingForm->getLabel('city'); ?>
			</div>
			<div class="controls">
				<?php echo $this->billingForm->getInput('city'); ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->billingForm->getLabel('zipcode'); ?>
			</div>
			<div class="controls">
				<?php echo $this->billingForm->getInput('zipcode'); ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->billingForm->getLabel('phone'); ?>
			</div>
			<div class="controls">
				<?php echo $this->billingForm->getInput('phone'); ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->billingForm->getLabel('email'); ?>
			</div>
			<div class="controls">
				<?php echo $this->billingForm->getInput('email'); ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->billingForm->getLabel('country'); ?>
			</div>
			<div class="controls">
				<?php echo $this->billingForm->getInput('country'); ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
			</div>
			<div class="controls">
				<button type="submit" id="updateBilling" class="btn btn-primary"><?php echo Text::_('JAPPLY'); ?></button>
			</div>
		</div>

		<!-- hidden fields -->
		<?php echo $this->billingForm->getInput('id'); ?>
		<input type="hidden" name="id" value="<?php echo $this->billing->id; ?>">
		<input type="hidden" name="task" value="billing.save">
		<input type="hidden" name="jform[cart_id]" value="<?php echo $this->item->id; ?>">
		<input type="hidden" name="return" value="<?php echo base64_encode('index.php?option=com_redform&view=cart&id=' . $this->item->id); ?>">
		<?php echo JHTML::_('form.token'); ?>
	</div>
</form>