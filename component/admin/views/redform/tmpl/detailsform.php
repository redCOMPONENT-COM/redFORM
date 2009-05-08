<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
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
