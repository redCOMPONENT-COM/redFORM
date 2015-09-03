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
$selectProperties = $data->getSelectProperties();
?>
<select <?php echo $data->propertiesToString($selectProperties); ?>>
	<?php foreach ($data->options as $option): ?>
		<?php $properties = $data->getOptionProperties($option); ?>
		<option <?php echo $data->propertiesToString($properties); ?>>
			<?php echo $option->label; ?>
		</option>
	<?php endforeach; ?>
</select>
