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

$nbfields = count($this->fields);
$colspan  = $nbfields + 5 + ($this->form->activatepayment ? 2 : 0 );
if (empty($this->integration) && $this->params->get('showintegration', false)) {
	$colspan++;
}
?>
<script type="text/javascript">
function submitbutton(pressbutton) {
	if (pressbutton == 'forcedelete') {
		if (confirm('<?php echo JText::_('COM_REDFORM_FORCEDELETE_ALERT'); ?>')) {
			submitform(pressbutton);
		}
	}
	else {
		submitform(pressbutton);
	}
} 
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<div class="button2-left">
		<div class="blank">
			<?php $csvlink = 'index.php?option=com_redform&controller=submitters&task=export'
			               . '&form_id=' . (empty($this->form) ? 0 : $this->form->id)
			               . (!empty($this->integration) ? '&integration='.$this->integration : '')
			               . ($this->xref ? '&xref='.$this->xref : '')
			               . '&format=raw'; 
			               ?>
			<?php echo JHTML::link($csvlink, JText::_('COM_REDFORM_CSV_EXPORT')); ?>
		</div>
	</div>
	<br clear="all" />
	<div id="formname"><?php echo (empty($this->form) ? JText::_('COM_REDFORM_All') : $this->form->formname); ?>
	<?php if ($this->coursetitle): ?><br /><?php echo $this->coursetitle; ?><?php endif; ?>
	</div>
  <?php if (!$this->xref): // if xref is set, prevent selecting another form ?>
	<table>
      <tr>
         <td align="left" width="100%">
            <?php echo JText::_('COM_REDFORM_Filter'); ?>:
			<?php echo $this->lists['form_id']; ?>
            <button onclick="this.form.submit();"><?php echo JText::_('COM_REDFORM_Go'); ?></button>
         </td>
      </tr>
    </table>
 <?php else: ?>
 <input type="hidden" name="form_id" value="<?php echo $this->form->id; ?>">
 <input type="hidden" name="xref" value="<?php echo $this->xref; ?>">
 <?php endif; ?>
<table class="adminlist">
	<!-- Headers -->
	<thead><tr>
	<th width="20"><?php echo JText::_('COM_REDFORM_ID'); ?></th>
	<th width="20"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->submitters ); ?>);" /></th>
	<th><?php echo JText::_('COM_REDFORM_Submission_date'); ?></th>
	<th><?php echo JText::_('COM_REDFORM_Form_name');?></th>
	<th><?php echo JText::_('COM_REDFORM_Unique_id');?></th>
	<?php if (!$this->integration && $this->params->get('showintegration', false)): ?>
	<th><?php echo JText::_('COM_REDFORM_Integration');?></th>
	<?php endif; ?>
	<?php foreach ($this->fields as $key => $value) { ?>
		<th><?php echo $value->field_header; ?></th>
	<?php } ?> 
	<?php if ($this->form->activatepayment): ?>
		<th width="20"><?php echo JText::_('COM_REDFORM_Price'); ?></th>
		<th width="20"><?php echo JText::_('COM_REDFORM_Payment'); ?></th>
	<?php endif;?>
	</tr></thead>
	
	<tfoot>
	<tr>
		<th colspan="<?php echo $colspan; ?>"><?php echo $this->pagination->getListFooter(); ?></th>
	 </tr>
	</tfoot>
	
	<tbody>
	<?php
	/* Data */
	$k = 1;
	if (count($this->submitters) > 0) 
	{
		foreach ($this->submitters as $id => $row) 
		{
			$link 	= 'index.php?option=com_redform&task=edit&controller=submitters&hidemainmenu=1&form_id='.$row->form_id.'&cid[]='. $row->sid;
			?>
			<tr class="row<?php echo $k = $k - 1; ?>">
				<td align="center">
					<?php echo $this->pagination->getRowOffset($id); ?>
				</td>
				<td>
					<input type="checkbox" onclick="isChecked(this.checked);" value="<?php echo $row->sid; ?>" name="cid[]" id="cb<?php echo $id; ?>"/>
				</td>
				<td><?php echo JHTML::link($link, $row->submission_date); ?></td>
				<td><?php echo $row->formname; ?></td>
				<?php if ($this->integration == 'redevent'): ?>
				<td><?php echo $this->course->uniqueid_prefix.$row->attendee_id;?></td>
				<?php else: ?>
				<td><?php echo $row->submit_key;?></td>
				<?php endif; ?>
				<?php if (!$this->integration && $this->params->get('showintegration', false)): ?>
				<td>
					<?php if ($row->xref || $row->integration): ?>
						<?php echo (!empty($row->integration) ? $row->integration : 'unspecified' );?>
					<?php else: ?>
					<?php endif; ?>
				<?php endif; ?>
				</td>
				<?php
				foreach ($this->fields as $key => $field) 
				{
					$fieldname = 'field_'. $field->id;
					if (isset($row->$fieldname)) 
					{
						$data = str_replace('~~~', '<br />', $row->$fieldname);
						if (stristr($data, JPATH_ROOT)) $data = '<a href="'.str_replace(DS, '/', str_replace(JPATH_ROOT, JURI::root(true), $data)).'" target="_blank">'.$data.'</a>';
						echo '<td>'.$data.'</td>';
					}
					else echo '<td></td>';
				}
				?>			
				<?php if ($this->form->activatepayment): ?>
					<td><?php echo $row->price; ?></td>
					<td class="price <?php echo ($row->paid ? 'paid' : 'unpaid'); ?>">
						<?php $link = JHTML::link(JRoute::_('index.php?option=com_redform&view=payments&submit_key='.$row->submit_key), JText::_('COM_REDFORM_history')); ?>
						<?php if (!$row->paid): ?>
						<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_REGISTRATION_NOT_PAID').'::'.$row->status; ?>"><?php echo JHTML::_('image.administrator', 'publish_x.png'); ?><?php echo $link; ?></span>
						<?php echo ' '.JHTML::link(JURI::root().'/index.php?option=com_redform&controller=payment&task=select&key='.$row->submit_key, JText::_('COM_REDFORM_link')); ?>
						<?php else: ?>
						<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_REGISTRATION_PAID').'::'.$row->status; ?>"><?php echo JHTML::_('image.administrator', 'tick.png'); ?><?php echo $link; ?></span>
						<?php endif; ?>						
					</td>
				<?php endif;?>
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
  <input type="hidden" name="view" value="submitters" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php if (JRequest::getInt('xref', false)) { ?><input type="hidden" name="xref" value="<?php echo JRequest::getInt('xref'); ?>" /><?php } ?>
	<?php if (!empty($this->integration)) { ?><input type="hidden" name="integration" value="<?php echo $this->integration; ?>" /><?php } ?>
	<input type="hidden" name="controller" value="submitters" />
</form>
