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
?>
<div class="fieldoptions">
	<?php foreach ($data->options as $option): ?>
		<div class="fieldoption">
			<?php $properties = $data->getOptionProperties($option); ?>
			<label class="radio" for="<?php echo $properties['value']; ?>">
				<input id="<?php echo $properties['value']; ?>" <?php echo $data->propertiesToString($properties); ?>/>
				<?php echo $option->label; ?>
			</label>
		</div>
	<?php endforeach; ?>
</div>
