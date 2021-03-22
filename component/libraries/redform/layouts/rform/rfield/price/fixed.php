<?php
/**
 * @package     Redform.Site
 * @subpackage  Layouts
 *
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * @var array $displayData data
 */
$data = $displayData;

$properties = $data->getInputProperties();
$options    = $data->options;
?>
<?php
if (count($options) == 1):
$option = $options[0];
$properties['type'] = 'hidden';
$properties['value'] = $option->value;
$properties['readonly'] = 'readonly';
?>
<input <?php echo $data->propertiesToString($properties); ?>/>
<?php echo $data->getCurrency() . ' ' . $option->value ; ?>
<?php else: ?>
	<select <?php echo $data->propertiesToString($properties); ?>>
		<?php foreach ($options as $option): ?>
			<option value="<?= $option->value ?>" price="<?= $option->value ?>"><?= $option->label ?></option>
		<?php endforeach; ?>
	</select>
<?php endif; ?>

