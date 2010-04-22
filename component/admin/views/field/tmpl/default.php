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
		<table class="adminform">
		<tr>
			<td>
			<?php echo JHTML::tooltip(JText::_('Enter the field here'), JText::_('Field'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Field'); ?>
			</td>
			<td>
			<input class="inputbox" type="text" size="80" name="field" value="<?php echo $this->row->field; ?>">
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
		<tr>
			<td>
			<?php echo JHTML::tooltip(JText::_('ENTER_TOOLTIP_INFO'), JText::_('ENTER_TOOLTIP'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('ENTER_TOOLTIP'); ?>
			</td>
			<td>
				<textarea name="tooltip" cols="80" rows="5"><?php echo $this->row->tooltip; ?></textarea>
			</td>
		</tr>
		<tr>
			<td>
			<?php echo JHTML::tooltip(JText::_('Set to yes to check if the field is filled in'), JText::_('Required'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Required'); ?>
			</td>
			<td>
			<?php echo $this->lists['validate']; ?>
			</td>
		</tr>
		<tr>
			<td>
			<?php echo JHTML::tooltip(JText::_('Set to yes to make the field unique, it can only appear once in the database. For example, only allow 1 registration per e-mail address.'), JText::_('Unique'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Unique'); ?>
			</td>
			<td>
			<?php echo $this->lists['unique']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right">
			<?php echo JHTML::tooltip(JText::_('Select the form the field belongs to. Changing forms REMOVES the field from the old form including all data.'), JText::_('Form'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Form'); ?>
			</td>
			<td>
			<?php echo $this->lists['forms']; ?>
			</td>
		</tr>
		<?php if (REDMEMBER_INTEGRATION): ?>
		<tr>
			<td valign="top" align="right">
			<?php echo JHTML::tooltip(JText::_('Select a redmember field to link to this field. This will allow to prefill data using redmember.'), JText::_('Redmember field'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Redmember field'); ?>
			</td>
			<td>
			<?php echo $this->lists['rmfields']; ?>
			</td>
		</tr>
		<?php endif; ?>
		<tr>
			<td valign="top" align="right">
			<?php echo JHTML::tooltip(JText::_('Set to Yes to make the field show on the form'), JText::_('Published'), 'tooltip.png', '', '', false); ?>
			<?php echo JText::_('Published'); ?>
			</td>
			<td>
			<?php echo $this->lists['published']; ?>
			</td>
		</tr>
		</table>
				
		<?php if ($this->parameters && $this->parameters->getGroups()): ?>		
		<?php
			foreach ( $this->parameters->getGroups() as $key => $groups )
			{
				$gname = ( strtolower($key) == '_default' ) ? JText::_( 'Extra' ) : $key;
				?>
				<fieldset class="adminform">
					<legend>
						<?php
						echo JText::_( $gname );
						?>
					</legend>
					<?php
					// render is defined in joomla\libraries\joomla\html\parameter.php
					echo $this->parameters->render( 'params', $key );
					?>
				</fieldset>
				<?php
			}
		?>
		<?php endif; ?>
		
  <?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
	<?php if ($this->state == 'disabled') { ?><input type="hidden" name="form_id" value="<?php echo $this->form_id; ?>" /><?php } ?>
	<input type="hidden" name="ordering" value="<?php echo $this->row->ordering; ?>" />
	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="fields" />
</form>