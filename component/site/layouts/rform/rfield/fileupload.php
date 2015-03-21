<?php
/**
 * @package     Redform.Site
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$data = $displayData;

$properties = $data->getInputProperties();

RHelperAsset::load('fileupload.js');
?>
<?php if ($data->getValue()) :?>
	<div class="current_file">
		<span class="upload-name"><?php echo basename($data->getValue()); ?></span>
		<input name="<?php echo $data->getFormElementName(); ?>_prev"
		       type="hidden" value="<?php echo $data->getValue(); ?>"/>
		<button type="button" class="remove-upload btn btn-danger btn-sm">
			<i class="icon-remove"></i>
		</button>
	</div>
<?php endif; ?>
<input <?php echo $data->propertiesToString($properties); ?>/>
