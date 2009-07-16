<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );
if ($this->countforms == 0) {
	echo JText::_('No forms found');
}
else { ?>
<form action="index.php" method="post" name="adminForm">
	<table>
      <tr>
         <td align="left" width="100%">
            <?php echo JText::_('Filter'); ?>:
			<?php echo $this->lists['form_id']; ?>
            <button onclick="this.form.submit();"><?php echo JText::_('Go'); ?></button>
         </td>
      </tr>
    </table>
	<table class="adminlist">
		<thead>
		<tr>
			<th width="20">
			<?php echo JText::_('ID'); ?>
			</th>
			<th width="20">
			<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->fields ); ?>);" />
			</th>
			<th class="title">
			<?php echo JText::_('Field'); ?>
			</th>
			<th class="title">
			<?php echo JText::_('Validate'); ?>
			</th>
			<th class="title">
			<?php echo JText::_('Unique'); ?>
			</th>
			<th class="title">
			<?php echo JText::_('Form'); ?>
			</th>
			<th class="title">
			<?php echo JText::_('Ordering'); ?>
			<?php echo JHTML::_('grid.order',  $this->fields ); ?>
			</th>
			<th class="title">
			<?php echo JText::_('Published'); ?>
			</th>
		</tr>
		</thead>
		<?php
		$k = 0;
		for ($i=0, $n=count( $this->fields ); $i < $n; $i++) {
			$row = $this->fields[$i];
			
			JFilterOutput::objectHTMLSafe($row);
			$link 	= 'index.php?option=com_redform&task=edit&controller=fields&hidemainmenu=1&cid[]='. $row->id;

			$img 	= $row->published ? 'tick.png' : 'publish_x.png';
			$task 	= $row->published ? 'unpublish' : 'publish';
			$alt 	= $row->published ? JText::_('Published') : JText::_('Unpublished');
			
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
					&nbsp;[ <i><?php echo JText::_('Checked Out'); ?></i> ]
					<?php
				} else {
					?>
					<a href="<?php echo $link; ?>" title="<?php echo JText::_('Edit field'); ?>">
					<?php echo $row->field; ?>
					</a>
					<?php
				}
				?>
				</td>
				<td>
					<?php 
					if ($row->validate) echo JText::_('Yes');
					else echo JText::_('No');
					?>
				</td>
				<td>
					<?php 
					if ($row->unique) echo JText::_('Yes');
					else echo JText::_('No');
					?>
				</td>
				<td>
					<?php echo $row->formname; ?>
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
	<input type="hidden" name="controller" value="fields" />
  <input type="hidden" name="view" value="fields" />
</form>
<?php } ?>
