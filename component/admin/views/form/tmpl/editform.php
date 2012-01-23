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
jimport('joomla.html.pane');
$editor =& JFactory::getEditor();
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<?php $pane   =& JPane::getInstance('tabs'); 
	echo $pane->startPane("settings");
	echo $pane->startPanel( JText::_('COM_REDFORM_Form'), 'form_tab' );
	$row = 0;
	?>
		<table class="adminform">
		<tr class="row<?php echo $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_FORM_NAME_TIP');?>"><?php echo JText::_('COM_REDFORM_FORM_NAME'); ?></span>
			</td>
			<td>
			<input class="inputbox" type="text" size="40" name="formname" value="<?php echo $this->row->formname; ?>"> 
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_FORM_SHOW_NAME_TIP');?>"><?php echo JText::_('COM_REDFORM_FORM_SHOW_NAME'); ?></span>
			</td>
			<td>
			 <?php echo $this->lists['showname']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_SET_ACCESS_LEVEL');?>"><?php echo JText::_('COM_REDFORM_ACCESS'); ?></span>
			</td>
			<td>
			 <?php echo $this->lists['access']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_FORM_CSS_CLASS_NAME_TIP');?>"><?php echo JText::_('COM_REDFORM_FORM_CSS_CLASS_NAME'); ?></span>
			</td>
			<td>
			 <input class="inputbox" type="text" size="40" name="classname" value="<?php echo $this->row->classname; ?>">
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_FORM_START_DATE_TIP');?>"><?php echo JText::_('COM_REDFORM_FORM_START_DATE'); ?></span>
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
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_FORM_EXPIRES_TIP');?>"><?php echo JText::_('COM_REDFORM_FORM_EXPIRES'); ?></span>
			</td>
			<td>
			<?php echo $this->lists['formexpires']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_FORM_END_DATE_TIP');?>"><?php echo JText::_('COM_REDFORM_FORM_END_DATE'); ?></span>
			</td>
			<td>
			<?php 
				$date = JFactory::getDate($this->row->enddate);
				echo JHTML::_('calendar', $date->toFormat('%d-%m-%Y  %H:%M:%S'), 'enddate', 'enddate', '%d-%m-%Y  %H:%M:%S', 'size="40"');
			?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td valign="top" align="right" class="hasTip" title="<?php echo JText::_('COM_REDFORM_CAPTCHA_ACTIVE'); ?>::<?php echo JText::_('COM_REDFORM_CAPTCHA_ACTIVE_TIP'); ?>">
			<?php echo JText::_('COM_REDFORM_CAPTCHA_ACTIVE'); ?>
			</td>
			<td>
			<?php echo $this->lists['captchaactive']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td valign="top" align="right">
			<?php echo JText::_('COM_REDFORM_Published'); ?>
			</td>
			<td>
			<?php echo $this->lists['published']; ?>
			</td>
		</tr>
		</table>
	<?php
	echo $pane->endPanel();
    echo $pane->startPanel( JText::_('COM_REDFORM_Notification'), 'notification_tab' );
	$row = 0;
	?>
		<table class="adminform">
		<tr class="row<?php echo $row;?>">
			<td valign="top" align="right">
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_FORM_NOTIFICATION_TIP');?>"><?php echo JText::_('COM_REDFORM_FORM_NOTIFICATION'); ?></span>
			</td>
			<td>
				<?php echo $this->lists['submitnotification']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td valign="top" align="right">
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_FORM_POST_SUBMISSION_TEXT_TIP');?>"><?php echo JText::_('COM_REDFORM_FORM_POST_SUBMISSION_TEXT'); ?></span>
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
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_REDIRECT_URL').'::'.JText::_('COM_REDFORM_REDIRECT_URL_TIP');?>"><?php echo JText::_('COM_REDFORM_REDIRECT_URL'); ?></span>
			</td>
			<td>
				<input name="redirect" type="text" value="<?php echo $this->row->redirect; ?>" size="80" />
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td valign="top" align="right">
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_NOTIFY_CONTACTPERSON_TIP');?>"><?php echo JText::_('COM_REDFORM_NOTIFY_CONTACTPERSON'); ?></span>
			</td>
			<td>
				<?php echo $this->lists['contactpersoninform']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td valign="top" align="right">
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_CONTACTPERSON_EMAIL_TIP');?>"><?php echo JText::_('COM_REDFORM_CONTACTPERSON_EMAIL'); ?></span>
			</td>
			<td>
				<input name="contactpersonemail" type="text" value="<?php echo $this->row->contactpersonemail; ?>" size="80" />
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td valign="top" align="right">
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_CONTACTPERSON_EMAIL_ADD_ANSWERS_TIP');?>"><?php echo JText::_('COM_REDFORM_CONTACTPERSON_EMAIL_ADD_ANSWERS'); ?></span>
			</td>
			<td>
				<?php echo $this->lists['contactpersonfullpost']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td valign="top" align="right">
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_NOTIFY_SUBMITTER_TIP');?>"><?php echo JText::_('COM_REDFORM_NOTIFY_SUBMITTER'); ?></span>
			</td>
			<td>
				<?php echo $this->lists['submitterinform']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td valign="top" align="right">
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_NOTIFY_SUBMITTER_EMAIL_SUBJECT_TIP');?>"><?php echo JText::_('COM_REDFORM_NOTIFY_SUBMITTER_EMAIL_SUBJECT'); ?></span>
			</td>
			<td>
				<input name="submissionsubject" type="text" value="<?php echo $this->row->submissionsubject; ?>" size="80" />
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td valign="top" align="right">
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_NOTIFY_SUBMITTER_EMAIL_BODY_TIP');?>"><?php echo JText::_('COM_REDFORM_NOTIFY_SUBMITTER_EMAIL_BODY'); ?></span>
			</td>
			<td>
			<?php echo $editor->display( "submissionbody", $this->row->submissionbody, 800, 300, 100, 30, array('pagebreak', 'readmore')); ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td valign="top" align="right">
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_NOTIFY_CONDITIONAL_RECIPIENTS_TIP');?>"><?php echo JText::_('COM_REDFORM_NOTIFY_CONDITIONAL_RECIPIENTS'); ?></span><br>
				<?php echo JText::_('COM_REDFORM_NOTIFY_CONDITIONAL_RECIPIENTS_TIP');?>
			</td>
			<td>
				<div id="cond_recipients_ui">
					<label for="cr_email"><?php echo JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_EMAIL_LABEL');?></label><input type="text" name="cr_email" id="cr_email"/> 
					<label for="cr_name"><?php echo JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_NAME_LABEL');?></label><input type="text" name="cr_name" id="cr_name"/> 
					<label for="cr_field"><?php echo JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FIELD_LABEL');?></label><?php echo $this->lists['cr_field']; ?> 
					<label for="cr_function"><?php echo JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FUNCTION_LABEL');?></label><?php echo $this->lists['cr_function']; ?> 
					<span id="cr_params"></span> 
					<button type="button" id="cr_button"><?php echo JText::_('COM_REDFORM_ADD');?></button>
				</div>
				<textarea name="cond_recipients" id="cond_recipients" rows="10" cols="80"><?php echo $this->row->cond_recipients; ?></textarea>
			</td>
		</tr>
		</table>
	<?php
	echo $pane->endPanel();
	echo $pane->startPanel( JText::_('COM_REDFORM_VIRTUEMART'), 'virtuemart_tab' );
	?>
	<table class="adminform">
		<tr class="row<?php echo $row; ?>">
			<td width="25%" valign="top" align="right">
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_VMACTIVE_TIP');?>"><?php echo JText::_('COM_REDFORM_VMACTIVE'); ?></span>
			</td>
			<td>
				<?php echo $this->lists['virtuemartactive']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_VMITEMID_TIP');?>"><?php echo JText::_('COM_REDFORM_VMITEMID'); ?></span>
			</td>
			<td>
				<input name="vmitemid" type="text" value="<?php echo $this->row->vmitemid; ?>" />
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_VMPRODUCTID_TIP');?>"><?php echo JText::_('COM_REDFORM_VMPRODUCTID'); ?></span>
			</td>
			<td>
				<?php echo $this->lists['vmproductid']; ?> 
			</td>
		</tr>
	</table>
	<?php
	echo $pane->endPanel();
	echo $pane->startPanel( JText::_('COM_REDFORM_PAYMENT'), 'payment_tab' );
	?>
	<table class="adminform">
		<tr class="row<?php echo $row; ?>">
			<td width="25%" valign="top" align="right">
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_PAYMENTACTIVE_TIP');?>"><?php echo JText::_('COM_REDFORM_PAYMENTACTIVE'); ?></span>
			</td>
			<td>
				<?php echo $this->lists['paymentactive']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row; ?>">
			<td width="25%" valign="top" align="right">
				<label for="show_js_price" class="hasTip" title="<?php echo JText::_('COM_REDFORM_FORM_SHOW_JS_PRICE_TIP');?>"><?php echo JText::_('COM_REDFORM_FORM_SHOW_JS_PRICE_LABEL'); ?></label>
			</td>
			<td>
				<?php echo $this->lists['show_js_price']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_PAYMENTCURRENCY_TIP');?>"><?php echo JText::_('COM_REDFORM_PAYMENTCURRENCY'); ?></span>
			</td>
			<td>
				<?php echo $this->lists['currency']; ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_PAYMENTPROCESSING_TIP');?>"><?php echo JText::_('COM_REDFORM_PAYMENTPROCESSING'); ?></span>
			</td>
			<td>
				<?php echo $editor->display( "paymentprocessing", $this->row->paymentprocessing, 800, 300, 100, 30, array('pagebreak', 'readmore')); ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_PAYMENTACCEPTED_TIP');?>"><?php echo JText::_('COM_REDFORM_PAYMENTACCEPTED'); ?></span>
			</td>
			<td>
				<?php echo $editor->display( "paymentaccepted", $this->row->paymentaccepted, 800, 300, 100, 30, array('pagebreak', 'readmore')); ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_PAYMENT_CONTACT_NOTIFICATION_EMAIL_SUBJECT_TIP');?>"><?php echo JText::_('COM_REDFORM_PAYMENT_CONTACT_NOTIFICATION_EMAIL_SUBJECT'); ?></span>
			</td>
			<td>
				<input name="contactpaymentnotificationsubject" type="text" value="<?php echo ($this->row->contactpaymentnotificationsubject ? $this->row->contactpaymentnotificationsubject : JText::_('COM_REDFORM_PAYMENT_CONTACT_NOTIFICATION_EMAIL_SUBJECT_DEFAULT')); ?>" size="80" />
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_PAYMENT_CONTACT_NOTIFICATION_EMAIL_BODY_TIP');?>"><?php echo JText::_('COM_REDFORM_PAYMENT_CONTACT_NOTIFICATION_EMAIL_BODY'); ?></span>
			</td>
			<td>
				<?php echo $editor->display( "contactpaymentnotificationbody",
				                             ($this->row->contactpaymentnotificationbody ? $this->row->contactpaymentnotificationbody : JText::_('COM_REDFORM_PAYMENT_CONTACT_NOTIFICATION_EMAIL_BODY_DEFAULT')),
				                             800, 300, 100, 30, array('pagebreak', 'readmore')); ?>
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_PAYMENT_SUBMITTER_NOTIFICATION_EMAIL_SUBJECT_TIP');?>"><?php echo JText::_('COM_REDFORM_PAYMENT_SUBMITTER_NOTIFICATION_EMAIL_SUBJECT'); ?></span>
			</td>
			<td>
				<input name="submitterpaymentnotificationsubject" type="text" value="<?php echo ($this->row->submitterpaymentnotificationsubject ? $this->row->submitterpaymentnotificationsubject : JText::_('COM_REDFORM_PAYMENT_SUBMITTER_NOTIFICATION_EMAIL_SUBJECT_DEFAULT')); ?>" size="80" />
			</td>
		</tr>
		<tr class="row<?php echo $row = 1 - $row; ?>">
			<td>
				<span class="hasTip" title="<?php echo JText::_('COM_REDFORM_PAYMENT_SUBMITTER_NOTIFICATION_EMAIL_BODY_TIP');?>"><?php echo JText::_('COM_REDFORM_PAYMENT_SUBMITTER_NOTIFICATION_EMAIL_BODY'); ?></span>
			</td>
			<td>
				<?php echo $editor->display( "submitterpaymentnotificationbody",                          
				                             ($this->row->submitterpaymentnotificationbody ? $this->row->submitterpaymentnotificationbody : JText::_('COM_REDFORM_PAYMENT_SUBMITTER_NOTIFICATION_EMAIL_BODY_DEFAULT')),
				                             800, 300, 100, 30, array('pagebreak', 'readmore')); ?>
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
