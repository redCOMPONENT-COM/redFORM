<?php
/**
 * @package    Redform.Site
 *
 * @copyright  Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

?>
<form action="<?php echo $this->action; ?>" method="post">
<fieldset>
	<legend><?php echo JText::_('COM_REDFORM_Payment')?></legend>
	<table class="rwf_payment">
		<tbody>
			<tr>
				<td><?php echo JText::_('COM_REDFORM_Total'); ?></td>
				<td><?php echo $this->currency . ' ' . $this->price; ?></td>
			</tr>
			<tr>
				<td><label for="gw"><?php echo JText::_('COM_REDFORM_Select_payment_method'); ?></label></td>
				<td><?php echo $this->lists['gwselect']; ?></td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="submit" value="<?php echo JText::_('COM_REDFORM_Continue'); ?>"/>
				</td>
			</tr>
		</tbody>
	</table>
</fieldset>

<input type="hidden" name="task" value="payment.process"/>
<input type="hidden" name="key" value="<?php echo $this->key; ?>"/>
<input type="hidden" name="source" value="<?php echo $this->source; ?>"/>
</form>
