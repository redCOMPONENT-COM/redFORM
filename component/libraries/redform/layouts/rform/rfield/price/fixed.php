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

$options = $data->options;
$option = $options[0];

$properties = $data->getInputProperties();
$properties['type'] = 'hidden';
$properties['value'] = $option->value;
$properties['readonly'] = 'readonly';
?>
<input <?php echo $data->propertiesToString($properties); ?>/>
<?php echo $data->getCurrency() . ' ' . $option->value ; ?>
