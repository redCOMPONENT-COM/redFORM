<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

?>
<fieldset class="batch">
	<legend><?php echo JText::_('COM_REDFORM_FIELDS_BATCH_OPTIONS');?></legend>
	<p><?php echo JText::_('COM_REDFORM_FIELDS_BATCH_OPTIONS_TIP'); ?></p>

	<label id="batch-choose-action-lbl" for="batch-field-id">
		<?php echo JText::_('COM_REDFORM_FIELDS_BATCH_FIELD_LABEL'); ?>
	</label>
	<fieldset id="batch-choose-action" class="combo">
	
	<label id="batch-field-lbl" for="batch_dest_form_id">
		<?php echo JText::_('COM_REDFORM_FIELDS_BATCH_SELECT_FORM_LABEL'); ?>
	</label>
	<select name="batch_dest_form_id" class="inputbox" id="batch_dest_form_id">
		<option value=""><?php echo JText::_('JSELECT') ?></option>
		<?php echo $this->lists['batch_form_options'];?>
	</select>
	</fieldset>

	<button type="submit" onclick="submitbutton('copy');">
		<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
	</button>
	<button type="button" onclick="document.id('batch-field-id').value='';">
		<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
	</button>
</fieldset>
