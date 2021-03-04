<?php
/**
 * @package     Redform.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$action = JRoute::_('index.php?option=com_redform&view=payment');
$return = \Joomla\CMS\Factory::getApplication()->input->getBase64('return');
?>
<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
      class="form-validate form-horizontal">
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('gateway'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('gateway'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('date'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('date'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('status'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('status'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('data'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('data'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('paid'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('paid'); ?>
		</div>
	</div>

	<!-- hidden fields -->
	<input type="hidden" name="option" value="com_redform">
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
	<input type="hidden" name="pr" value="<?php echo $this->state->get('payment_request'); ?>">
	<?php echo $this->form->getInput('cart_id'); ?>
	<?php echo $this->form->getInput('id'); ?>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="return" value="<?= $return ?>">
	<?php echo JHTML::_('form.token'); ?>
</form>
