<?php
/**
 * @package     Redform
 * @subpackage  mod_redform_latest_submissions
 *
 * @copyright   Copyright (C) 2008 - 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

RHelperAsset::load('style.min.css', 'mod_redform_latest_submissions');

$moduleId = $module->id;
$moduleSelector = 'mod_redform_latest_submissions_' . $moduleId;
?>
<div id="<?= $moduleSelector ?>" class="mod_aesir_top_items box">
	<div class="box-header with-border">
		<h3 class="box-title">
			<span>
				<i class="fa fa-file-text"></i><?= JText::_('MOD_REDFORM_LATEST_SUBMISSIONS_BOX_TITLE') ?>
			</span>
		</h3>
	</div>

	<div class="box-body">
		<table class="table">
			<thead>
				<tr>
					<th class="nowrap hidden-phone">
						<?= JText::_('COM_REDFORM_Form_name'); ?>
					</th>
					<th width="1%" class="nowrap hidden-phone">
						<?= JText::_('COM_REDFORM_ID') ?>
					</th>
					<th class="nowrap hidden-phone">
						<?= JText::_('COM_REDFORM_Submission_date'); ?>
					</th>
					<th class="nowrap hidden-phone">
						<?= JText::_('COM_REDFORM_confirmed_HEADER'); ?>
					</th>
					<th class="nowrap hidden-phone">
						<?= JText::_('COM_REDFORM_Unique_id'); ?>
					</th>

					<?php if ($params->get('showintegration', false)): ?>
						<th class="nowrap hidden-phone">
							<?= JText::_('COM_REDFORM_Integration'); ?>
						</th>
					<?php endif; ?>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($list as $item): ?>
					<tr>
						<td><a href="index.php?option=com_redform&view=submitters&filter[form_id]=<?= $item->form_id ?>"><?= $item->formname ?></a></td>
						<td><?= $item->id ?></td>
						<td><?= JHTML::Date($item->submission_date, 'Y-m-d H:i:s') ?></td>
						<td><?= $item->confirmed_date ?></td>
						<td><?= $item->submit_key ?></td>
						<?php if ($params->get('showintegration', true)): ?>
							<td><?= $item->integration ?></td>
						<?php endif; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>

		</table>
	</div>
</div>
