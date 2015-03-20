<?php
/**
 * @package    Redform.Admin
 *
 * @copyright  Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$data = $displayData;

RHelperAsset::load('conditionalrecipients.js', 'com_redform');
?>
<div id="cond_recipients_ui">
	<div class="row">
		<div class="span5 form-horizontal">
			<div class="form-group">
				<div class="control-label">
					<label for="cr_email">
						<?php echo JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_EMAIL_LABEL'); ?>
					</label>
				</div>
				<div class="controls">
					<input type="text" name="cr_email" id="cr_email"
					       placeholder="<?php echo JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_EMAIL_LABEL'); ?>"/>
				</div>
			</div>

			<div class="form-group">
				<div class="control-label">
					<label for="cr_name">
						<?php echo JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_NAME_LABEL'); ?>
					</label>
				</div>
				<div class="controls">
					<input type="text" name="cr_name" id="cr_name"
					       placeholder="<?php echo JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_NAME_LABEL'); ?>"/>
				</div>
			</div>

			<div class="form-group">
				<div class="control-label">
					<label for="cr_field">
						<?php echo JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FIELD_LABEL'); ?>
					</label>
				</div>
				<div class="controls">
					<select name="cr_field" id="cr_field" class="form-control"></select>
				</div>
			</div>

			<div class="form-group">
				<div class="control-label">
					<label for="cr_function">
						<?php echo JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FUNCTION_LABEL'); ?>
					</label>
				</div>
				<div class="controls">
					<?php echo JHTML::_('select.genericlist', $data['functionsOptions'], 'cr_function', 'class="form-control"'); ?>
					<div><span id="cr_params"></span></div>
				</div>
			</div>
		</div>

		<div class="span1">
				<button type="button" id="cr_button" class="btn btn-primary btn-lg"><?php echo JText::_('COM_REDFORM_ADD');?></button>
		</div>

		<div class="span5">
			<div class="conditional-recipients">
				<?php echo $data['textarea']; ?>
			</div>
		</div>
	</div>
</div>
