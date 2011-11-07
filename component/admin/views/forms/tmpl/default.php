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

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );?>
<form action="index.php" method="post" name="adminForm">

	<table>
      <tr>
         <td align="left" width="100%">
            <?php echo JText::_('COM_REDFORM_Filter'); ?>:
						<?php echo $this->lists['state']; ?>
         </td>
      </tr>
    </table>

	<table class="adminlist">
		<thead>
		<tr>
			<th width="20">#</th>
			<th width="20">
			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->forms ); ?>);" />
			</th>
			<th class="title">
				<?php echo JHTML::_('grid.sort', 'COM_REDFORM_Form_name', 'formname', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
			<th class="title">
				<?php echo JHTML::_('grid.sort', 'COM_REDFORM_FORM_START_DATE', 'startdate', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
			<th class="title">
				<?php echo JHTML::_('grid.sort', 'COM_REDFORM_FORM_END_DATE', 'enddate', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
			<th class="title">
			<?php echo JText::_('COM_REDFORM_Published'); ?>
			</th>
			<th class="title">
			<?php echo JText::_('COM_REDFORM_Active'); ?>
			</th>
			<th class="title">
			<?php echo JText::_('COM_REDFORM_Submitters'); ?>
			</th>
			<th class="title">
			<?php echo JText::_('COM_REDFORM_Tag'); ?>
			</th>
			<th width="20">
				<?php echo JHTML::_('grid.sort', 'COM_REDFORM_ID', 'id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php
		$k = 0;
		for ($i=0, $n=count( $this->forms ); $i < $n; $i++) {
			$row = $this->forms[$i];
			
			JFilterOutput::objectHTMLSafe($row);
			$link 	= 'index.php?option=com_redform&task=edit&controller=forms&cid[]='. $row->id;

			if ($row->published >= 0) {
				$published 	= JHTML::_('grid.published', $row, $i );
			}
			else {
				$published = JHTML::image('administrator/images/publish_y.png', JText::_('COM_REDFORM_ARCHIVED'));
			}
			$form 	= $row->formstarted ? 'tick.png' : 'publish_x.png';
			
			$checked = JHTML::_('grid.checkedout',  $row, $i);
			$my  = JFactory::getUser();
			
			if (isset($this->submitters[$row->id])) {
				$submitters = JHTML::link('index.php?option=com_redform&view=submitters&form_id='.$row->id, $this->submitters[$row->id]->total);
			}
			else {
				$submitters = '0';
			}
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
					<?php echo $row->formname; ?>
					&nbsp;[ <i><?php echo JText::_('COM_REDFORM_Checked_Out'); ?></i> ]
					<?php
				} else {
					?>
					<a href="<?php echo $link; ?>" title="<?php echo JText::_('COM_REDFORM_Edit_form'); ?>">
					<?php echo $row->formname; ?>
					</a>
					<?php
				}
				?>
				</td>
				<td>
				<?php
					$date = JFactory::getDate($row->startdate);
					echo $date->toFormat('%d-%m-%Y  %H:%M:%S');
				?>
				</td>
				<td>
				<?php if ($row->formexpires) {
					$date = JFactory::getDate($row->enddate);
					echo $date->toFormat('%d-%m-%Y  %H:%M:%S');
				}
				?>
				</td>
				<td width="10%" align="center"><?php echo $published; ?></td>
				<td width="10%" align="center">
				<img src="images/<?php echo $form;?>" border="0" alt="<?php echo JText::_('COM_REDFORM_Form_started'); ?>" />
				</td>
				<td>
				<?php echo $submitters;	?>
				</td>
				<td>
				<?php 
					echo "{redform}".$row->id."{/redform}";
				?>
				</td>
				<td><?php echo $row->id; ?></td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		<tr>
            <td colspan="10"><?php echo $this->pagination->getListFooter(); ?></td>
         </tr>
         
		</tbody>
		</table>
	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="forms" />
	<input type="hidden" name="view" value="forms" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
</form>
