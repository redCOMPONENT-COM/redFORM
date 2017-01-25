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

	<?php if ($submission->getForm()->params->get('allow_frontend_edit', 0)): ?>
		<div class="'edit-link">
			<?= JHtml::link(RdfHelperRoute::getSubmissionRoute($submission->id) . '&task=submission.edit',
				JText::_('COM_REDFORM_VIEW_SUBMISSION_EDIT_LINK')); ?>
		</div>
	<?php endif; ?>

	<table class="table form-answers">
		<thead>
			<tr>
				<th><?= JText::_('COM_REDFORM_VIEW_SUBMISSION_COL_FIELD') ?></th>
				<th><?= JText::_('COM_REDFORM_VIEW_SUBMISSION_COL_ANSWER') ?></th>
			</tr>
		</thead>
		<?php foreach ($answers->getFields() as $field): ?>
			<tr>
				<th class="field-name"><?= $field->getLabel() ?></th>
				<td><?= $field->getValueAsString() ?></td>
			</tr>
		<?php endforeach; ?>
	</table>

	<div class="return-button">
		<a class="btn btn-primary" href="<?= JRoute::_(RdfHelperRoute::getMysubmissionsRoute()) ?>"><?= JText::_('COM_REDFORM_VIEW_SUBMISSION_BACK') ?></a>
	</div>
</div>
