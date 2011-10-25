<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license GNU/GPL, see LICENSE.php
 * redFORM can be downloaded from www.redcomponent.com
 * redFORM is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redFORM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redFORM; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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

<input type="hidden" name="task" value="process"/>
<input type="hidden" name="key" value="<?php echo $this->key; ?>"/>
<input type="hidden" name="source" value="<?php echo $this->source; ?>"/>
</form>