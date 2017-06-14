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
?>
<label for="<?php echo $field->getFormElementName(); ?>">
	<?= $field->field; ?>
	<?php if ($field->required && !$field->readonly): ?>
		<span class="label-field-required"><?= JText::_('LIB_REDFORM_FIELD_REQUIRED') ?></span>
	<?php endif; ?>
</label>
