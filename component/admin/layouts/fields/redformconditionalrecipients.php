<?php
/**
 * @package    Redform.Admin
 *
 * @copyright  Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$data = $displayData;

$fields = array();

$fields['functions'] = array('label' => JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FUNCTION_LABEL'),
	'field' => JHTML::_('select.genericlist', $data['functionsOptions'], 'cr_function', 'class="form-control"')
);

$fields['email'] = array('label' => JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_EMAIL_LABEL'),
	'field' => '<input type="text" name="cr_email" id="cr_email" placeholder="' . JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_EMAIL_LABEL') . '"/>'
);

$fields['name'] = array('label' => JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_NAME_LABEL'),
	'field' => '<input type="text" name="cr_name" id="cr_name" placeholder="' . JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_NAME_LABEL') . '"/>'
);

$fields['field'] = array('label' => JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FIELD_LABEL'),
	'field' => '<select name="cr_field" id="cr_field"></select>'
);

RHelperAsset::load('conditionalrecipients.js', 'com_redform');
?>
<div id="cond_recipients_ui" class="container-fluid">
	<div class="row">
		<div class="col-sm-6 form-horizontal">
			<div class="form-group">
				<label for="cr_email" class="col-sm-3 control-label">
					<?php echo JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_EMAIL_LABEL'); ?>
				</label>
				<div class="col-sm-9">
					<input type="text" name="cr_email" id="cr_email" class="form-control"
					       placeholder="<?php echo JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_EMAIL_LABEL'); ?>"/>
				</div>
			</div>

			<div class="form-group">
				<label for="cr_name" class="col-sm-3 control-label">
					<?php echo JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_NAME_LABEL'); ?>
				</label>
				<div class="col-sm-9">
					<input type="text" name="cr_name" id="cr_name" class="form-control"
					       placeholder="<?php echo JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_NAME_LABEL'); ?>"/>
				</div>
			</div>

			<div class="form-group">
				<label for="cr_field" class="col-sm-3 control-label">
					<?php echo JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FIELD_LABEL'); ?>
				</label>
				<div class="col-sm-9">
					<select name="cr_field" id="cr_field" class="form-control"></select>
				</div>
			</div>

			<div class="form-group">
				<label for="cr_function" class="col-sm-3 control-label">
					<?php echo JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FUNCTION_LABEL'); ?>
				</label>
				<div class="col-sm-9">
					<?php echo JHTML::_('select.genericlist', $data['functionsOptions'], 'cr_function', 'class="form-control"'); ?>
					<div><span id="cr_params"></span></div>
				</div>
			</div>
		</div>

		<div class="col-sm-1">
				<button type="button" id="cr_button" class="btn btn-primary btn-lg"><?php echo JText::_('COM_REDFORM_ADD');?></button>
		</div>

		<div class="col-sm-5">
			<div class="conditional-recipients">
				<?php echo $data['textarea']; ?>
			</div>
		</div>
	</div>
</div>
