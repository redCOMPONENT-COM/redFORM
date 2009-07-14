<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );
JHTML::_('behavior.tooltip');
jimport('joomla.html.pane');
$editor =& JFactory::getEditor();
?>
<form action="index.php" method="post" name="adminForm">
	<?php $pane   =& JPane::getInstance('tabs'); 
	echo $pane->startPane("settings");
	echo $pane->startPanel( JText::_('Form'), 'form_tab' );
	$row = 0;
	?>
		<table class="adminform">
		<tr class="row<?php echo $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('Give the form a name');?>"><?php echo JText::_('Form name'); ?></span>
			</td>
			<td>
			<input class="inputbox" type="text" size="40" name="formname" value="<?php echo $this->row->formname; ?>"> 
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('Set to show the form name on the form');?>"><?php echo JText::_('Show form name'); ?></span>
			</td>
			<td>
			 <?php echo $this->lists['showname']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('SET_ACCESS_LEVEL');?>"><?php echo JText::_('ACCESS'); ?></span>
			</td>
			<td>
			 <?php echo $this->lists['access']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('Set class name to allow individual styling');?>"><?php echo JText::_('CSS class name'); ?></span>
			</td>
			<td>
			 <input class="inputbox" type="text" size="40" name="classname" value="<?php echo $this->row->classname; ?>">
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('Set a date on which the form should start');?>"><?php echo JText::_('Start date'); ?></span>
			</td>
			<td>
			<?php 
				$date = JFactory::getDate($this->row->startdate);
				echo JHTML::_('calendar', $date->toFormat('%d-%m-%Y  %H:%M:%S'), 'startdate', 'startdate', '%d-%m-%Y  %H:%M:%S', 'size="40"');
			?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('Set to never make a form expire');?>"><?php echo JText::_('Form Expires'); ?></span>
			</td>
			<td>
			<?php echo $this->lists['formexpires']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('Set a date on which the form should end');?>"><?php echo JText::_('End date'); ?></span>
			</td>
			<td>
			<?php 
				$date = JFactory::getDate($this->row->enddate);
				echo JHTML::_('calendar', $date->toFormat('%d-%m-%Y  %H:%M:%S'), 'enddate', 'enddate', '%d-%m-%Y  %H:%M:%S', 'size="40"');
			?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td valign="top" align="right">
			<?php echo JText::_('CAPTCHA_ACTIVE'); ?>
			</td>
			<td>
			<?php echo $this->lists['captchaactive']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td valign="top" align="right">
			<?php echo JText::_('Published'); ?>
			</td>
			<td>
			<?php echo $this->lists['published']; ?>
			</td>
		</tr>
		</table>
	<?php
	echo $pane->endPanel();
    echo $pane->startPanel( JText::_('Notification'), 'notification_tab' );
	$row = 0;
	?>
		<table class="adminform">
		<tr class="row<?php echo $row;?>">
			<td valign="top" align="right">
				<span class="hasTip" title="<?php echo JText::_('Give a notification after a form has been submitted');?>"><?php echo JText::_('Notify on submission'); ?></span>
			</td>
			<td>
				<?php echo $this->lists['submitnotification']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td valign="top" align="right">
				<span class="hasTip" title="<?php echo JText::_('Text to show as notification after a form has been submitted');?>"><?php echo JText::_('Notification text'); ?></span>
			</td>
			<td>
				<?php
				$editor =& JFactory::getEditor();
				echo $editor->display( "notificationtext", $this->row->notificationtext, 800, 300, 100, 30, array('pagebreak', 'readmore') );
				?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td valign="top" align="right">
				<span class="hasTip" title="<?php echo JText::_('Inform contactperson on new submission');?>"><?php echo JText::_('Inform contactperson'); ?></span>
			</td>
			<td>
				<?php echo $this->lists['contactpersoninform']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td valign="top" align="right">
				<span class="hasTip" title="<?php echo JText::_('E-mail address of the contactperson');?>"><?php echo JText::_('E-mail contactperson'); ?></span>
			</td>
			<td>
				<input name="contactpersonemail" type="text" value="<?php echo $this->row->contactpersonemail; ?>" size="80" />
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td valign="top" align="right">
				<span class="hasTip" title="<?php echo JText::_('Send all posted values to contact person on submission. If set to know only a notification will be send.');?>"><?php echo JText::_('Send form data to contactperson'); ?></span>
			</td>
			<td>
				<?php echo $this->lists['contactpersonfullpost']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td valign="top" align="right">
				<span class="hasTip" title="<?php echo JText::_('Inform submitter of submission via e-mail?<br /><br />This requires an email field in your form.');?>"><?php echo JText::_('E-mail submitter'); ?></span>
			</td>
			<td>
				<?php echo $this->lists['submitterinform']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td valign="top" align="right">
				<span class="hasTip" title="<?php echo JText::_('Subject of notificaton send to new submitters');?>"><?php echo JText::_('Notification subject new submitters'); ?></span>
			</td>
			<td>
				<input name="submissionsubject" type="text" value="<?php echo $this->row->submissionsubject; ?>" size="80" />
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td valign="top" align="right">
				<span class="hasTip" title="<?php echo JText::_('Body text of notificaton send to new submitters');?>"><?php echo JText::_('Notification body new submitters'); ?></span>
			</td>
			<td>
			<?php echo $editor->display( "submissionbody", $this->row->submissionbody, 800, 300, 100, 30, array('pagebreak', 'readmore')); ?>
			</td>
		</tr>
		</table>
	<?php
	echo $pane->endPanel();
	echo $pane->startPanel( JText::_('VIRTUEMART'), 'virtuemart_tab' );
	?>
	<table class="adminform">
		<tr class="row<?php echo $row; ?>">
			<td width="25%" valign="top" align="right">
				<span class="hasTip" title="<?php echo JText::_('VMACTIVE_TIP');?>"><?php echo JText::_('VMACTIVE'); ?></span>
			</td>
			<td>
				<?php echo $this->lists['virtuemartactive']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('VMITEMID_TIP');?>"><?php echo JText::_('VMITEMID'); ?></span>
			</td>
			<td>
				<input name="vmitemid" type="text" value="<?php echo $this->row->vmitemid; ?>" />
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('VMPRODUCTID_TIP');?>"><?php echo JText::_('VMPRODUCTID'); ?></span>
			</td>
			<td>
				<?php echo $this->lists['vmproductid']; ?> 
			</td>
		</tr>
	</table>
	<?php
	echo $pane->endPanel();
	echo $pane->endPane();
	?>
  <?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="forms" />
</form>
