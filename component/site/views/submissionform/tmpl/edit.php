<?php
/**
 * @package    Redform.Site
 *
 * @copyright  Copyright (C) 2008 - 2017 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$submission = RdfEntitySubmitter::getInstance($this->item->id);
$submission->bind($this->item);

$answers = $submission->getAnswers();
$form = $submission->getForm();
$rfcore = RdfCore::getInstance($submission->form_id);
?>
<div class="redform-submissions<?php echo $this->pageclass_sfx;?>">
	<?php if ($this->params->get('show_page_heading') != 0) : ?>
		<div class="page-header">
			<h1>
				<?php echo $this->escape($this->params->get('page_heading')); ?>
			</h1>
		</div>
	<?php endif; ?>

	<div class="well">
		<ul>
			<li><?= JText::_('COM_REDFORM_VIEW_SUBMISSION_META_FORM_NAME') ?>: <?= $submission->getForm()->formname ?></li>
			<li><?= JText::_('COM_REDFORM_VIEW_SUBMISSION_META_SUBMITTED_ON') ?>: <?= $submission->getDate('submission_date', 'F j, Y, H:i') ?></li>
		</ul>
	</div>

	<form action="<?php echo JRoute::_('index.php?option=com_redform&id=' . $submission->id); ?>" method="post" name="redform"
	      id="adminForm" enctype="multipart/form-data" class="form-validate form-horizontal">

		<?php echo $rfcore->getFormFields($submission->form_id, array($submission->id), 1); ?>

		<div class="btn-group submitform <?php echo $form->classname; ?>">
			<button id="submit_button" type="button" class="btn btn-primary" onclick="Joomla.submitbutton('submission.save')">
				<span class="icon-ok"></span><?php echo $form->params->get('submit_label') ?: JText::_('COM_REDFORM_Submit'); ?>
			</button>
		</div>
		<div  class="btn-group cancelform <?php echo $form->classname; ?>">
			<button type="button" class="btn" onclick="Joomla.submitbutton('submission.cancel')">
				<span class="icon-cancel"></span><?php echo JText::_('JCANCEL') ?>
			</button>
		</div>

		<input type="hidden" name="task" id="task" value="save" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
