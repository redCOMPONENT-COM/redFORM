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
?>
<label for="<?php echo $field->getFormElementName(); ?>">
	<?= $field->field; ?>
	<?php if ($field->required && !$field->readonly): ?>
		<span class="label-field-required"><?= JText::_('LIB_REDFORM_FIELD_REQUIRED') ?></span>
	<?php endif; ?>
</label>
