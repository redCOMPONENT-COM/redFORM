<?php
/**
 * @package     Redform.Site
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$field = $displayData;

$class = array();

$text = $field->field;

if ($field->required)
{
	$text .= ' <span class="star">*</span>';
	$class[] = 'required';
}

if ($tooltip = $field->tooltip)
{
	$text = RHtml::tooltip($field->tooltip, '', null, $text);
}

$class = implode(" ", $class);
?>
<label for="<?php echo $field->getFormElementName(); ?>" <?php echo $class; ?>><?php echo $text; ?></label>
