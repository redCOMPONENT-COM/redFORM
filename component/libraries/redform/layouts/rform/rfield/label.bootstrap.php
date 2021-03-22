<?php
/**
 * @package     Redform.Site
 * @subpackage  Layouts
 *
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$field = $displayData;

$class = '';

if ($field->required && !$field->readonly)
{
	$class = 'class = "required"';
}

?>
<label for="<?php echo $field->getFormElementName(); ?>" <?= $class ?>>
	<?= $field->field; ?>
	<?php if ($field->required && !$field->readonly): ?>
		<span class="label-field-required"><?= JText::_('LIB_REDFORM_FIELD_REQUIRED') ?></span>
	<?php endif; ?>
</label>
