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
	<?php foreach ($answers->getAnswers() as $answer):
		if (is_array($answer['value']))
		{
			$value = implode('<br>', $answer['value']);
		}
		elseif ($answer['type'] == 'file')
		{
			$value = basename($answer['value']);
		}
		else
		{
			$value = $answer['value'];
		}
	?>
	<tr>
		<th><?php echo $answer['field']; ?></th>
		<td><?php echo $value; ?></td>
	</tr>
	<?php endforeach; ?>

	<?php if ($p = $answers->getPrice()): ?>
	<tr>
		<th><?php echo JText::_('COM_REDFORM_TOTAL_PRICE'); ?></th>
		<td><?php echo $p; ?></td>
	</tr>
	<?php endif; ?>
</table>
