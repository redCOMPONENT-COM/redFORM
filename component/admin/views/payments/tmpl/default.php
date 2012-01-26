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

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	
<table class="adminlist">
	<!-- Headers -->
	<thead>
	<tr>
		<th width="20"><?php echo JText::_('COM_REDFORM_ID'); ?></th>
		<th width="20"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->rows ); ?>);" /></th>
		<th><?php echo JText::_('COM_REDFORM_Date'); ?></th>
		<th width="20"><?php echo JText::_('COM_REDFORM_Gateway'); ?></th>
		<th width="20"><?php echo JText::_('COM_REDFORM_Status'); ?></th>
		<th><?php echo JText::_('COM_REDFORM_Info'); ?></th>
		<th width="20"><?php echo JText::_('COM_REDFORM_Paid'); ?></th>
	</tr>
	</thead>
	
	<tfoot>
	<tr>
		<th colspan="7"><?php echo $this->pagination->getListFooter(); ?></th>
	 </tr>
	</tfoot>
	
	<tbody>
	<?php
	/* Data */
	$k = 1;
	if (count($this->rows) > 0) 
	{
		foreach ($this->rows as $id => $value) 
		{
			$edit_link = JRoute::_('index.php?option=com_redform&controller=payments&task=edit&cid[]='.$value->id.'&submit_key='.$this->key);
			?>
			<tr class="row<?php echo $k = $k - 1; ?>">
				<td align="center">
					<?php echo $this->pagination->getRowOffset($id); ?>
				</td>
				<td>
					<input type="checkbox" onclick="isChecked(this.checked);" value="<?php echo $value->id; ?>" name="cid[]" id="cb<?php echo $id; ?>"/>
				</td>
				<td><?php echo (empty($value->gateway) ? JHTML::link($edit_link, $value->date) : $value->date); ?></td>
				<td><?php echo $value->gateway; ?></td>
				<td><?php echo $value->status; ?></td>
				<td><?php echo str_replace("\n", "<br />",$value->data); ?></td>
				<td><?php echo $value->paid; ?></td>
			</tr>
			<?php 
			$k++;
		}
	}
	
	?>
	</tbody>
</table>

	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="" />
  <input type="hidden" name="view" value="payments" />
  <input type="hidden" name="submit_key" value="<?php echo $this->key; ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="payments" />
</form>
