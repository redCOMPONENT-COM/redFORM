<?php
/**
 * @package     Redform.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$data = $displayData;

$app = JFactory::getApplication();

$options = $data['options'];
$fields = $data['fields'];
$answers = $data['answers'];
$user = $data['user'];
$index = $data['index'];
$form = $data['form'];
$multi = $data['multi'];

$html = '';

if (isset($options['extrafields'][$index]))
{
	$fields = array_merge($options['extrafields'][$index], $fields);
}

$sections = RdfHelper::sortFieldBySection($fields);

RHelperAsset::load('punycode.js');
RHelperAsset::load('formsteps.js', 'com_redform');
RHelperAsset::load('formsteps.css', 'com_redform');
RHelperAsset::load('showon.js', 'com_redform');
?>
<?php if ($multi > 1): ?>
	<fieldset><legend class="subform-legend"><?= JText::sprintf('COM_REDFORM_FIELDSET_SIGNUP_NB', $index) ?></legend>
		<div class="subform-fields">
<?php endif; ?>

<?php foreach ($sections as $s): ?>
	<?php $section = RdfEntitySection::load($s->id); ?>
	<fieldset class="redform-section<?= $section->class ? ' ' . $section->class : '' ?>">

		<?php foreach ($s->fields as $field): ?>
			<?php $field->setForm($form); ?>
			<?php if ($field->isHidden()): ?>
				<?= $field->getInput() ?>
			<?php else:
				$rel = '';
				$class = "control-group type-" . $field->fieldtype . $field->getParam('class', '');

				if ($showon = $field->getParam('showon'))
				{
					$showon = explode(':', $showon, 2);

					// We need the form field id, from the field id given in parameters
					$targetField = RdfHelper::findFormFieldByFieldId($fields, $showon[0]);
					$class .= ' rfshowon_' . implode(' rfshowon_', explode(',', $showon[1]));
					$rel = ' rel="rfshowon_field' . $targetField->id . '_' . $index . '"';
				}
				?>

				<div class="<?= $class ?>" <?= $rel ?>>

					<?php if ($field->displayLabel()): ?>
						<div class="control-label">
							<?= $field->getLabel() ?>
							<?php if (!empty($field->tooltip)): ?>
								<div class="label-field-tip"><?= $field->tooltip ?></div>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<div class="controls field"><?= $field->getInput() ?></div>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>

	</fieldset>
<?php endforeach; ?>

<?= $this->sublayout('progressbar', $sections) ?>

<?php if ($multi > 1): ?>
		</div>
	</fieldset>
<?php endif;
