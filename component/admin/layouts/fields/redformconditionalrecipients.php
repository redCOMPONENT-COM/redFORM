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
	<div class="control-group">
		<div class="control-label">
			<?php echo $data->fields['email']['label']; ?>
		</div>
		<div class="controls">
			<?php echo $data->fields['email']['field']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $data->fields['name']['label']; ?>
		</div>
		<div class="controls">
			<?php echo $data->fields['name']['field']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $data->fields['field']['label']; ?>
		</div>
		<div class="controls">
			<?php echo $data->fields['field']['field']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $data->fields['functions']['label']; ?>
		</div>
		<div class="controls">
			<?php echo $data->fields['functions']['field']; ?>
			<div><span id="cr_params"></span></div>
		</div>
	</div>
	<div class="control-group button-add">
		<div class="controls">
		<button type="button" id="cr_button" class="btn btn-primary btn-lg"><?php echo JText::_('COM_REDFORM_ADD');?></button>
		</div>
	</div>
</div>
<div class="conditional-recipients">
	<?php echo $data->textarea; ?>
</div>
