<?php
/**
 * @package     Redform.Site
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$data = $displayData;
?>
<div class="fieldoptions">
	<fieldset class="checkboxes<?php echo $data->required ? ' required' : ''; ?>">
		<?php foreach ($data->options as $option): ?>
			<?php $properties = $data->getOptionsProperties($option); ?>
			<div class="fieldoption">
				<input <?php echo $data->propertiesToString($properties); ?>/> <?php echo $option->text; ?>
			</div>
		<?php endforeach; ?>
	</fieldset>
</div>
