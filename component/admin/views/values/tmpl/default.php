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
if ($this->fields == 0) {
	echo JText::_('COM_REDFORM_No_fields_found');
}
else { ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table>
      <tr>
         <td align="left" width="100%">
            <?php echo JText::_('COM_REDFORM_Filter'); ?>:
			<?php echo $this->lists['form_id']; ?>
            <button onclick="this.form.submit();"><?php echo JText::_('COM_REDFORM_Go'); ?></button>
         </td>
      </tr>
    </table>
	<table class="adminlist">
		<tr>
			<th width="20">
			<?php echo JText::_('COM_REDFORM_ID'); ?>
			</th>
			<th width="20">
			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->values ); ?>);" />
			</th>
			<th class="title">
			<?php echo JText::_('COM_REDFORM_Value'); ?>
			</th>
			<th class="title">
			<?php echo JText::_('COM_REDFORM_Field'); ?>
			</th>
			<th class="title">
			<?php echo JText::_('COM_REDFORM_FIELD_TYPE'); ?>
			</th>
			<th class="title">
			<?php echo JText::_('COM_REDFORM_Price'); ?>
			</th>
			<th class="title">
			<?php echo JText::_('COM_REDFORM_Ordering'); ?>
			<?php echo JHTML::_('grid.order',  $this->values ); ?>
			</th>
			<th class="title">
			<?php echo JText::_('COM_REDFORM_Published'); ?>
			</th>
		</tr>
		<?php
		$k = 0;
		for ($i=0, $n=count( $this->values ); $i < $n; $i++) {
			$row = &$this->values[$i];
			
			JFilterOutput::objectHTMLSafe($row);
			$link 	= 'index2.php?option=com_redform&task=edit&controller=values&hidemainmenu=1&cid[]='. $row->id;

			$img 	= $row->published ? 'tick.png' : 'publish_x.png';
			$task 	= $row->published ? 'unpublish' : 'publish';
			$alt 	= $row->published ? JText::_('COM_REDFORM_Published') : JText::_('COM_REDFORM_Unpublished');
			
			$checked = JHTML::_('grid.checkedout',  $row, $i);
			$my  = &JFactory::getUser();
			?>
			<tr class="<?php echo 'row'. $k; ?>">
				<td align="center">
				<?php echo $this->pagination->getRowOffset($i); ?>
				</td>
				<td>
				<?php echo $checked; ?>
				</td>
				<td>
				<?php
				if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
					?>
					<?php echo $row->value; ?>
					&nbsp;[ <i><?php echo JText::_('COM_REDFORM_Checked_Out'); ?></i> ]
					<?php
				} else {
					?>
					<a href="<?php echo $link; ?>" title="<?php echo JText::_('COM_REDFORM_Edit_field'); ?>">
					<?php 
					 if ($row->fieldtype == 'info') {
					   $val = JFilterInput::clean($row->value, 'string');
					   if (strlen($val) > 40) {
					     $val = substr($val, 0, 47) . '...';
					   }
					   echo $val;
				   }
				   else {
				     echo $row->value;
				   }
				  ?>
					</a>
					<?php
				}
				?>
				</td>
				<td>
					<?php echo $row->fieldname; ?>
				</td>
				<td>
					<?php echo JText::_($row->fieldtype); ?>
				</td>
				<td>
					<?php echo $row->price; ?>
				</td>
				<td>
					<input type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" class="text_area" style="text-align: center" />
				</td>
				<td width="10%" align="center">
				<a href="javascript: void(0);" onClick="return listItemTask('cb<?php echo $i;?>','<?php echo $task;?>')">
				<img src="images/<?php echo $img;?>" border="0" alt="<?php echo $alt; ?>" />
				</a>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		<tr>
            <td colspan="8"><?php echo $this->pagination->getListFooter(); ?></td>
         </tr>
		</table>
	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="values" />
  <input type="hidden" name="view" value="values" />
</form>
<?php } ?>