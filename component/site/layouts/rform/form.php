<?php
/**
 * @package     Redform.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$data = $displayData;

$app = JFactory::getApplication();

if (isset($data['action']))
{
	$action = $data['action'];
}
else
{
	$action = JRoute::_('index.php?option=com_redform');
}
$form = $data['form'];
$sid = $data['sid'];
$fieldsHtml = $data['fieldsHtml'];
$referer64 = $data['referer64'];
?>
<form action="<?php echo $action; ?>" method="post" name="redform" class="form-validate <?php echo $form->classname; ?>" enctype="multipart/form-data">

	<?php echo $fieldsHtml; ?>

	<?php if (!$sid): ?>
		<div id="submit_button" style="display: block;" class="submitform <?php echo $form->classname; ?>">
			<input type="submit" id="regularsubmit" name="submit" class="validate" value="<?php echo JText::_('COM_REDFORM_Submit'); ?>" />
		</div>
	<?php else: ?>
		<input type="hidden" name="submitter_id" value="<?php echo $sid; ?>" />
	<?php endif; ?>

	<input type="hidden" name="task" value="redform.save" />
	<input type="hidden" name="referer" value="<?php echo $referer64; ?>" />
</form>
