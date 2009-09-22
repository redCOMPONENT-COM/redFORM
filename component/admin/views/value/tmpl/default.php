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
JHTML::_('behavior.tooltip');
?>
<form action="index.php" method="post" name="adminForm">
		<table id="editvalue" class="adminform">
		<tr>
			<td>
			<?php echo JText::_('Value'); ?>
			</td>
			<td>
			<?php if ($this->row->fieldtype == 'info'): ?>
			<?php echo $this->editor->display( 'value',  $this->row->value, '100%;', '300', '75', '20', array('pagebreak', 'readmore') ) ; ?>
			<?php else: ?>
			<input class="inputbox" type="text" size="80" name="value" value="<?php echo $this->row->value; ?>">
	    <?php endif; ?>
			<?php echo JHTML::tooltip(JText::_('Enter the value here'), JText::_('Value'), 'tooltip.png', '', '', false); ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right">
			<?php echo JText::_('Field'); ?>
			</td>
			<td>
			<?php echo $this->lists['fields']; ?>
			</td>
		</tr>
		<tr id="trfieldtypes">
			<td valign="top" align="right">
			<?php echo JText::_('Field type'); ?>
			</td>
			<td>
			<div id="newfieldtype">
			<?php echo $this->lists['fieldtypes']; ?>
			</div>
			</td>
		</tr>
		<?php
		if (isset($this->mailinglists) && !empty($this->uselists)) {
			$listnames = explode(';', $this->mailinglists->listnames);
			?>
			<tr class="row1" id="trmailinglist">
				<td valign="top" align="right">
					<?php echo JHTML::tooltip(JText::_('NEWSLETTERS_TIP'), JText::_('NEWSLETTERS'), 'tooltip.png', '', '', false); ?>
					<?php echo JText::_('NEWSLETTERS'); ?>
				</td>
				<td>
					<div id="newmailinglist">
						<select id="mailinglist" name="mailinglist">
							<?php
								$newsletters = array('Acajoom', 'ccNewsletter', 'PHPList');
								foreach ($newsletters as $key => $name) {
									if (in_array('use_'.strtolower($name), $this->uselists)) { 
										$option = '<option value="'.strtolower($name).'"';
										if (strtolower($name) == $this->mailinglists->mailinglist) $option .= 'selected="selected"';
										$option .= '>'.$name.'</option>';
										echo $option;
									}
								}
							?>
						</select>
					</div>
				</td>
			</tr>
			<tr id="traddlists">
				<td>
					<?php echo JHTML::tooltip(JText::_('ADD_LISTS_TIP'), JText::_('ADD_LISTS'), 'tooltip.png', '', '', false); ?>
					<?php echo JText::_('ADD_LISTS'); ?>
				</td>
				<td>
					<a href="#" onClick="addFormField(); return false;"><?php echo JText::_('ADD_LIST'); ?></a>
				</td>
			</tr>
			<?php
				foreach ($listnames as $key => $name) { ?>
					<tr id="listnamerow<?php echo $key; ?>">
						<td valign="top" align="right">
							<?php echo JHTML::tooltip(JText::_('LISTNAME_TIP'), JText::_('LISTNAME'), 'tooltip.png', '', '', false); ?>
							<?php echo JText::_('LISTNAME'); ?>
						</td>
						<td>
							<input type="text" id="listname<?php echo $key; ?>" name="listname[]" value="<?php echo $name; ?>"/>&nbsp;
							<a href="#" onClick="removeFormField('#listnamerow<?php echo $key; ?>'); return false;"><?php echo JText::_('REMOVE_LIST'); ?></a>
						</td>
					</tr>
			<?php }
		}
		?>
		<tr id="trpublished">
			<td valign="top" align="right">
			<?php echo JText::_('Published'); ?>
			</td>
			<td>
			<?php echo $this->lists['published']; ?>
			</td>
		</tr>
		</table>
  <?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
	<input type="hidden" name="ordering" value="<?php echo $this->row->ordering; ?>" />
	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="values" />
	<input type="hidden" name="form_id" value="<?php echo JRequest::getInt('form_id', 0); ?>" />
	<input type="hidden" id="listnameid" value="1">
</form>
<script type="text/javascript" charset="utf-8">
jQuery(function(){
	jQuery("select#fieldtype").change(function(){
			if (jQuery(this).val() == 'email') {
				jQuery("#trmailinglist").show();
				
				jQuery("#traddlists").show();
				
				/* Hide all lists fields */
				jQuery("[id^='listnamerow']").each(function(i) {
					jQuery("#listnamerow"+i).show();
				
				})
				
				if (jQuery("#trmailinglist").length == 0) {
					jQuery('#trfieldtypes~#trpublished').before('<tr class="row1" id="trmailinglist"><td valign="top" align="right"><?php echo JText::_('NEWSLETTERS'); ?></td><td><div id="newmailinglist"><select name="mailinglist" id="mailinglist"></select></div></td></tr><tr id="traddlists"><td><?php echo JText::_('ADD_LISTS'); ?></td><td><a href="#" onClick="addFormField(); return false;"><?php echo JText::_('ADD_LIST'); ?></a></td></tr>');
					var oldvalue = jQuery("select#mailinglist").val();
					
					jQuery.getJSON("index.php",{option: 'com_redform', controller: 'values', task: 'getmailinglist',  format: 'json'}, function(j){
						var options = '';
						for (var i = 0; i < j.length; i++) {
							options += '<option value="' + j[i].optionValue + '"';
							if (j[i].optionValue == oldvalue) options += 'selected="selected"';
							options += '>' + j[i].optionDisplay + '</option>';
						}
						jQuery("select#mailinglist").html(options);
					})
				}
			}
			else {
				jQuery("#trmailinglist").hide();
				
				jQuery("#traddlists").hide();
				
				/* Hide all lists fields */
				jQuery("[id^='listnamerow']").each(function(i) {
					jQuery(this).hide();
				
				})
			}
			
	})
})

function addFormField() {
	var id = document.getElementById("listnameid").value;
	jQuery('#trpublished').before('<tr class="row1" id="listnamerow'+id+'"><td valign="top" align="right"><?php echo JText::_('Listname'); ?></td><td><input type="text" id="listname'+id+'" name="listname[]" /> <a href="#" onClick="removeFormField(\'#listnamerow'+id+'\'); return false;"><?php echo JText::_('REMOVE_LIST'); ?></a></td></tr>');
	
	id = (id - 1) + 2;
	document.getElementById("listnameid").value = id;
}
function removeFormField(id) {
	jQuery(id).remove();
}
jQuery("table#editvalue tr:even").addClass("row0");
jQuery("table#editvalue tr:odd").addClass("row1");
</script>