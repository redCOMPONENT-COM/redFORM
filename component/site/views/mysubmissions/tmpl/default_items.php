<?php
/**
 * @package    Redform.Site
 *
 * @copyright  Copyright (C) 2008 - 2017 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;
?>
<table class="table">
	<thead>
		<tr>
			<th><?= JText::_('COM_REDFORM_VIEW_MYSUBMISSIONS_COL_DATE') ?></th>
			<th><?= JText::_('COM_REDFORM_VIEW_MYSUBMISSIONS_COL_FORM') ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($this->items as $submission): ?>
			<tr>
				<td><?= $submission->getDate('submission_date', $this->params->get('date_format', 'F j, Y, H:i')) ?></td>
				<td><?= $submission->getForm()->formname ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
