<?php
/**
 * @package    Redform.Site
 *
 * @copyright  Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

?>
<h1><?php echo JText::_('COM_REDFORM_Payment')?></h1>
<form action="<?php echo $this->action; ?>" method="post">
<fieldset>
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
<input type="hidden" name="reference" value="<?php echo $this->reference; ?>"/>
<input type="hidden" name="source" value="<?php echo $this->source; ?>"/>
</form>
