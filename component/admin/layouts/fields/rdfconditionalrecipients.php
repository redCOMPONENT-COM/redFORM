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
	<div><?php echo $data->fields['email']['label'] . $data->fields['email']['field']; ?></div>
	<div><?php echo $data->fields['name']['label'] . $data->fields['name']['field']; ?></div>
		<div><?php echo $data->fields['field']['label'] . $data->fields['field']['field']; ?></div>
			<div><?php echo $data->fields['functions']['label'] . $data->fields['functions']['field']; ?>
				<div><span id="cr_params"></span></div>
			</div>
	<button type="button" id="cr_button"><?php echo JText::_('COM_REDFORM_ADD');?></button>
</div>
<?php echo $data->textarea; ?>
