<?php
/**
 * @package     Redform.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

JHtml::_('rjquery.chosen', 'select');
$action = JRoute::_('index.php?option=com_redform&view=formfield');
?>
<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
      class="form-validate form-horizontal">
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('field_id'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('field_id'); ?>
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
			<?php echo $this->form->getLabel('published'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('published'); ?>
		</div>
	</div>

	<!-- hidden fields -->
	<input type="hidden" name="option" value="com_redform">
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
	<?php echo $this->form->getInput('form_id'); ?>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="return" value="<?php echo JFactory::getApplication()->input->get('return'); ?>">
	<?php echo JHTML::_('form.token'); ?>
</form>
