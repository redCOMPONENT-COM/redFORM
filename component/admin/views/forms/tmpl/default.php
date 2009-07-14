<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );?>
<form action="index.php" method="post" name="adminForm">
	<table class="adminlist">
		<tr>
			<th width="20">
			<?php echo JText::_('ID'); ?>
			</th>
			<th width="20">
			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->forms ); ?>);" />
			</th>
			<th class="title">
			<?php echo JText::_('Form name'); ?>
			</th>
			<th class="title">
			<?php echo JText::_('Start date'); ?>
			</th>
			<th class="title">
			<?php echo JText::_('End date'); ?>
			</th>
			<th class="title">
			<?php echo JText::_('Published'); ?>
			</th>
			<th class="title">
			<?php echo JText::_('Form started'); ?>
			</th>
			<th class="title">
			<?php echo JText::_('Submitters'); ?>
			</th>
			<th class="title">
			<?php echo JText::_('Tag'); ?>
			</th>
		</tr>
		<?php
		$k = 0;
		for ($i=0, $n=count( $this->forms ); $i < $n; $i++) {
			$row = $this->forms[$i];
			
			JFilterOutput::objectHTMLSafe($row);
			$link 	= 'index.php?option=com_redform&task=edit&controller=forms&cid[]='. $row->id;

			$img 	= $row->published ? 'tick.png' : 'publish_x.png';
			$task 	= $row->published ? 'unpublish' : 'publish';
			$alt 	= $row->published ? JText::_('Published') : JText::_('Unpublished');
			$form 	= $row->formstarted ? 'tick.png' : 'publish_x.png';
			
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
					<?php echo $row->formname; ?>
					&nbsp;[ <i><?php echo JText::_('Checked Out'); ?></i> ]
					<?php
				} else {
					?>
					<a href="<?php echo $link; ?>" title="<?php echo JText::_('Edit form'); ?>">
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
				<?php 
					$date = JFactory::getDate($row->enddate);
					echo $date->toFormat('%d-%m-%Y  %H:%M:%S');
				?>
				</td>
				<td width="10%" align="center">
				<a href="javascript: void(0);" onClick="return listItemTask('cb<?php echo $i;?>','<?php echo $task;?>')">
				<img src="images/<?php echo $img;?>" border="0" alt="<?php echo $alt; ?>" />
				</a>
				</td>
				<td width="10%" align="center">
				<img src="images/<?php echo $form;?>" border="0" alt="<?php echo JText::_('Form started'); ?>" />
				</td>
				<td>
				<?php
					if (isset($this->submitters[$row->id])) echo $this->submitters[$row->id]->total;
					else echo '0';
				?>
				</td>
				<td>
				<?php 
					echo "{redform}".$row->id."{/redform}";
				?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		<tr>
            <td colspan="9"><?php echo $this->pagination->getListFooter(); ?></td>
         </tr>
		</table>
	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="forms" />
</form>
