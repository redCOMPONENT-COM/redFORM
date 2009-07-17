<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );?>
<form action="index.php" method="post" name="adminForm">
	<div class="button2-left">
		<div class="blank">
			<a title="<?php echo JText::_('CSV EXPORT'); ?>" onclick="window.open('index.php?option=com_redform&controller=submitters&task=export&form_id=<?php echo (empty($this->form) ? 0 : $this->form->id) ;?>&format=raw')"><?php echo JText::_('CSV EXPORT'); ?></a>
		</div>
	</div>
	<br clear="all" />
	<div id="formname"><?php echo (empty($this->form) ? JText::_('All') : $this->form->formname); ?>
	<?php if ($this->coursetitle): ?><br /><?php echo $this->coursetitle; ?><?php endif; ?>
	</div>
  <?php if (!$this->xref): // if xref is set, prevent selecting another form ?>
	<table>
      <tr>
         <td align="left" width="100%">
            <?php echo JText::_('Filter'); ?>:
			<?php echo $this->lists['form_id']; ?>
            <button onclick="this.form.submit();"><?php echo JText::_('Go'); ?></button>
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
	<th width="20"><?php echo JText::_('ID'); ?></th>
	<th width="20"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->submitters ); ?>);" /></th>
	<th><?php echo JText::_('Submission date'); ?></th>
	<th><?php echo JText::_('Form name');?></th>
	<?php foreach ($this->fields as $key => $value) { ?>
		<th><?php echo $value->field; ?></th>
	<?php } ?> 
	</tr></thead>
	<tbody>
	<?php
	/* Data */
	$nbfields = count($this->fields);
	$k = 1;
	if (count($this->submitters) > 0) 
	{
		foreach ($this->submitters as $id => $value) 
		{
			?>
			<tr class="row<?php echo $k = $k - 1; ?>">
			<td align="center">
				<?php echo $this->pagination->getRowOffset($id); ?>
			</td>
			<td>
				<input type="checkbox" onclick="isChecked(this.checked);" value="<?php echo $value->id; ?>" name="cid[]" id="cb<?php echo $id; ?>"/>
			</td>
			<td><?php echo $value->submission_date; ?></td>
			<td><?php echo $value->formname; ?></td>
			<?php
			foreach ($this->fields as $key => $field) 
			{
				$fieldname = 'field_'. $field->id;
				if (isset($value->$fieldname)) 
				{
					$data = str_replace('~~~', '<br />', $value->$fieldname);
					if (stristr($data, JPATH_ROOT)) $data = '<a href="'.str_replace(DS, '/', str_replace(JPATH_ROOT, JURI::root(true), $data)).'" target="_blank">'.$data.'</a>';
					echo '<td>'.$data.'</td>';
				}
				else echo '<td></td>';
			}
			echo '</tr>';
			$k++;
		}
	}
	
	?>
	</tbody>
	<tfoot>
	<tr>
		<th colspan="<?php echo $nbfields+4;?>"><?php echo $this->pagination->getListFooter(); ?></th>
	 </tr>
	 </tfoot>
</table>
	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="" />
  <input type="hidden" name="view" value="submitters" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php if (JRequest::getInt('xref', false)) { ?><input type="hidden" name="xref" value="<?php echo JRequest::getInt('xref'); ?>" /><?php } ?>
	<input type="hidden" name="controller" value="submitters" />
</form>
