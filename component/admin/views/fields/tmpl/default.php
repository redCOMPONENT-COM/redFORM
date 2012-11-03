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
if ($this->countforms == 0) {
	echo JText::_('COM_REDFORM_No_forms_found');
}
else { ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table>
      <tr>
         <td align="left" width="100%">
            <?php echo JText::_('COM_REDFORM_Filter'); ?>:
			<?php echo $this->lists['form_id']; ?>
         </td>
      </tr>
    </table>
	<table class="adminlist">
		<thead>
		<tr>
			<th width="20">
			<?php echo JText::_('#'); ?>
			</th>
			<th width="20">
			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->fields ); ?>);" />
			</th>
			<th class="title"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_Field', 'field', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th class="title"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_FIELD_HEADER', 'field_header', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th class="title"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_Type', 'fieldtype', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th class="title"><?php echo JText::_('COM_REDFORM_Required'); ?></th>
			<th class="title"><?php echo JText::_('COM_REDFORM_Unique'); ?></th>
			<th class="title"><?php echo JText::_('COM_REDFORM_Form'); ?></th>
			<th width="10%">
				<?php echo JHTML::_('grid.sort', 'COM_REDFORM_Ordering', 'ordering', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				<?php echo JHTML::_('grid.order',  $this->fields ); ?>
			</th>
			<th width="5%"><?php echo JText::_('COM_REDFORM_Published'); ?></th>
			<th width="5%"><?php echo JHTML::_('grid.sort', 'COM_REDFORM_ID', 'id', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		$k = 0;
		for ($i=0, $n=count( $this->fields ); $i < $n; $i++) {
			$row = $this->fields[$i];
			
			JFilterOutput::objectHTMLSafe($row);
			$link 	= 'index.php?option=com_redform&task=edit&controller=fields&hidemainmenu=1&cid[]='. $row->id;
			
			$checked = JHTML::_('grid.checkedout',  $row, $i);
			$my  = JFactory::getUser();
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
					<?php echo $row->field; ?>
					&nbsp;[ <i><?php echo JText::_('COM_REDFORM_Checked_Out'); ?></i> ]
					<?php
				} else {
					?>
					<a href="<?php echo $link; ?>" title="<?php echo JText::_('COM_REDFORM_Edit_field'); ?>">
					<?php echo $row->field; ?>
					</a>
					<?php
				}
				?>
				</td>
				<td>
					<?php echo $row->field_header;	?>
				</td>
				<td>
					<?php echo $row->fieldtype;	?>
				</td>
				<td>
					<?php 
					if ($row->validate) echo JText::_('JYES');
					else echo JText::_('JNO');
					?>
				</td>
				<td>
					<?php 
					if ($row->unique) echo JText::_('JYES');
					else echo JText::_('JNO');
					?>
				</td>
				<td>
					<?php echo $row->formname; ?>
				</td>
				<td class="order">
					<?php if ($this->lists['order'] == 'ordering'): ?>
	          <span><?php echo $this->pagination->orderUpIcon( $i, true, 'orderup', 'Move Up', $row->ordering ); ?></span>
	  
	          <span><?php echo $this->pagination->orderDownIcon( $i, $n, true, 'orderdown', 'Move Down', $row->ordering );?></span>
	  
	          <?php $disabled = $row->ordering ?  '' : '"disabled=disabled"'; ?>
						<input type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" class="text_area" style="text-align: center" />
					<?php else : ?>
						<?php echo $row->ordering; ?>
					<?php endif; ?>
				</td>
				<td width="10%" align="center">
					<?php echo JHtml::_('jgrid.published', $row->published, $i); ?>
				</td>
				<td><?php echo $row->id; ?></td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>		
		</tbody>
		<tfoot>
		<tr>
            <td colspan="11"><?php echo $this->pagination->getListFooter(); ?></td>
         </tr>
		</tfoot>
		</table>
		
	<?php //Load the batch processing form. ?>
	<?php echo $this->loadTemplate('batch'); ?>
	
	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="fields" />
  <input type="hidden" name="view" value="fields" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
</form>
<?php } ?>
