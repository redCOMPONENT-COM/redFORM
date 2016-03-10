<?php
/**
 * @package     Redform.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$action = JRoute::_('index.php?option=com_redform&view=section');
?>
<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
      class="form-validate form-horizontal">
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('name'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('name'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('class'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('class'); ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('description'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('description'); ?>
		</div>
	</div>

	<!-- hidden fields -->
	<input type="hidden" name="option" value="com_redform">
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
	<?php echo $this->form->getInput('id'); ?>
	<input type="hidden" name="task" value="">
	<?php echo JHTML::_('form.token'); ?>
</form>
