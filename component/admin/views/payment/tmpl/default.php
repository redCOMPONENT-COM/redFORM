<?php
/**
 * @version    $Id: form.php 94 2008-05-02 10:28:05Z julienv $
 * @package    JoomlaTracks
 * @copyright	Copyright (C) 2008 Julien Vonthron. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla Tracks is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col50">
<fieldset class="adminform"><legend><?php echo JText::_('COM_REDFORM_Payment' ); ?></legend>

<table class="admintable">
	<tr>
		<td width="100" align="right" class="key">
			<label><?php echo JText::_('COM_REDFORM_Date' ); ?>:</label>
		</td>
		<td>
			<?php echo $this->object->date; ?>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key">
			<label><?php echo JText::_('COM_REDFORM_Gateway' ); ?>:</label>
		</td>
		<td>
			<?php echo $this->object->gateway; ?>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key">
			<label><?php echo JText::_('COM_REDFORM_Status' ); ?>:</label>
		</td>
		<td>
			<?php echo $this->object->status; ?>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key">
			<label><?php echo JText::_('COM_REDFORM_Data' ); ?>:</label>
		</td>
		<td>
			<?php echo str_replace("\n", "<br/>",$this->object->data); ?>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key">
			<label><?php echo JText::_('COM_REDFORM_Paid' ); ?>:</label>
		</td>
		<td>
			<?php echo $this->object->paid; ?>
		</td>
	</tr>
</table>
</fieldset>

</div>

<input type="hidden" name="option" value="com_redform" /> 
<input type="hidden" name="controller" value="payments" /> 
<input type="hidden" name="cid[]" value="<?php echo $this->object->id; ?>" />
<input type="hidden" name="task" value="" />

</form>