<?php
/**
 * @package     Redform.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$action = JRoute::_('index.php?option=com_redform&view=payment');
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
	<input type="hidden" name="submit_key" value="<?php echo $this->item->submit_key; ?>">
	<?php echo $this->form->getInput('submit_key'); ?>
	<input type="hidden" name="task" value="">
	<?php echo JHTML::_('form.token'); ?>
</form>
