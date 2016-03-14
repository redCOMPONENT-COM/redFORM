<?php
/**
 * @package     Redform.Site
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$answers = $displayData;
?>
<table>
	<?php foreach ($answers->getFields() as $field): ?>
	<tr>
		<th><?php echo $field->field; ?></th>
		<td><?php echo $field->getValueAsString('<br/>'); ?></td>
	</tr>
	<?php endforeach; ?>

	<?php if ($p = $answers->getPrice()): ?>
	<tr>
		<th><?php echo JText::_('COM_REDFORM_TOTAL_PRICE'); ?></th>
		<td><?php echo $p; ?></td>
	</tr>
	<?php endif; ?>

	<?php if ($v = $answers->getVat()): ?>
		<tr>
			<th><?php echo JText::_('COM_REDFORM_VAT'); ?></th>
			<td><?php echo $v; ?></td>
		</tr>
	<?php endif; ?>
</table>
