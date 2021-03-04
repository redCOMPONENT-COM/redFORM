<?php
/**
 * @package     Redform.Site
 * @subpackage  Layouts
 *
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$data = $displayData;
?>
<div class="fieldoptions">

	<?php foreach ($data->options as $option): ?>
		<div class="fieldoption">
			<?php $properties = $data->getOptionProperties($option); ?>
			<input <?php echo $data->propertiesToString($properties); ?>/>
			<?php echo $option->label; ?>
		</div>
	<?php endforeach; ?>

</div>
