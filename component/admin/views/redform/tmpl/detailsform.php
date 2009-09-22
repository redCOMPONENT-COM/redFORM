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
<?php $row = 0; ?>
<form action="index.php" method="post" name="adminForm">
	<table class="adminform">
		<tr class="row<?php echo $row;?>"><td style="width: 25%;"><?php echo JText::_('Competition name'); ?></td><td><?php echo $this->form->formname; ?></td></tr>
		<?php $date = JFactory::getDate($this->form->startdate); ?>
		<tr class="row<?php echo $row = 1 - $row; ?>"><td><?php echo JText::_('Start date'); ?></td><td><?php echo $date->toFormat('%d-%m-%Y  %H:%M:%S'); ?></td></tr>
		<?php $date = JFactory::getDate($this->form->enddate); ?>
		<tr class="row<?php echo $row = 1 - $row; ?>"><td><?php echo JText::_('End date'); ?></td><td><?php echo $date->toFormat('%d-%m-%Y  %H:%M:%S'); ?></td></tr>
		<tr class="row<?php echo $row = 1 - $row; ?>"><td><?php echo JText::_('Published'); ?></td><td><?php $published = ($this->form->published) ? JText::_('Yes') : JText::_('No'); echo $published; ?></td></tr>
		<tr class="row<?php echo $row = 1 - $row; ?>"><td><?php echo JText::_('Form started'); ?></td><td><?php $started = ($this->form->formstarted) ? JText::_('Yes') : JText::_('No'); echo $started; ?></td></tr>
		<?php if (!empty($this->form->mailinglistname)) { ?><tr class="row<?php echo $row = 1 - $row; ?>"><td><?php echo JText::_('Mailinglist'); ?></td><td><?php echo $this->form->mailinglistname; ?></td></tr><?php }?>
		<tr class="row<?php echo $row = 1 - $row; ?>"><td><?php echo JText::_('Mailinglist Sign ups'); ?></td><td><?php echo $this->newsletter; ?></td></tr>
		<tr class="row<?php echo $row = 1 - $row; ?>"><td><?php echo JText::_('Submitters'); ?></td><td><?php echo count($this->submitters); ?></td></tr>
	</table>
	<input type="hidden" name="id" value="<?php echo $this->form->id; ?>" />
	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="redform" />
	<input type="hidden" name="controller" value="redform" />
</form>
