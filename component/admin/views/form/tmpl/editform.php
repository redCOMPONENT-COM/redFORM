<?php
/**
 * @package    redform
 * @copyright  Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license    GNU/GPL, see LICENSE.php
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

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

JHTML::_('behavior.tooltip');
$editor = JFactory::getEditor();
$row = 0;
?>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
	<div class="span10 form-horizontal">
	<fieldset>
		<ul class="nav nav-tabs">
			<li class="active"><a href="#details" data-toggle="tab"><?php echo JText::_('COM_REDFORM_Form');?></a></li>
			<li><a href="#notification" data-toggle="tab"><?php echo JText::_('COM_REDFORM_Notification');?></a></li>
			<li><a href="#payment" data-toggle="tab"><?php echo JText::_('COM_REDFORM_PAYMENT');?></a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="details">
				<?php foreach ($this->form->getFieldset('details') as $field): ?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $field->label; ?>
					</div>
					<div class="controls">
						<?php echo $field->input; ?>
					</div>
				</div>
				<?php endforeach; ?>
			</div>

			<div class="tab-pane" id="notification">
				<?php foreach ($this->form->getFieldset('notification') as $field): ?>
				<?php if ($field->id == 'cond_recipients') continue; ?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $field->label; ?>
					</div>
					<div class="controls">
						<?php echo $field->input; ?>
					</div>
				</div>
				<?php endforeach; ?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('cond_recipients'); ?>
					</div>
					<div class="controls">
						<div id="cond_recipients_ui">
							<label for="cr_email"><?php echo JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_EMAIL_LABEL');?></label><input type="text" name="cr_email" id="cr_email"/>
							<label for="cr_name"><?php echo JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_NAME_LABEL');?></label><input type="text" name="cr_name" id="cr_name"/>
							<label for="cr_field"><?php echo JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FIELD_LABEL');?></label><?php echo $this->lists['cr_field']; ?>
							<label for="cr_function"><?php echo JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FUNCTION_LABEL');?></label><?php echo $this->lists['cr_function']; ?>
							<span id="cr_params"></span>
							<button type="button" id="cr_button"><?php echo JText::_('COM_REDFORM_ADD');?></button>
						</div>
						<textarea name="cond_recipients" id="cond_recipients" rows="10" cols="80"><?php echo $this->row->cond_recipients; ?></textarea>
					</div>
				</div>
			</div>

			<div class="tab-pane" id="payment">
				<?php foreach ($this->form->getFieldset('payment') as $field): ?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $field->label; ?>
					</div>
					<div class="controls">
						<?php echo $field->input; ?>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	</fieldset>
	</div>

	<?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="forms" />
</form>
