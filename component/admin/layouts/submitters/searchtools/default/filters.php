<?php
/**
 * @package     Redcore
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

if (is_array($data['options']))
{
	$data['options'] = new JRegistry($data['options']);
}

$searchField  = 'filter_' . $data['options']->get('searchField', 'search');

// Load the form filters
$filters = $data['view']->filterForm->getGroup('filter');
?>
<?php if ($filters) : ?>
	<?php foreach ($filters as $fieldName => $field) : ?>
		<?php if ( !in_array($fieldName, array($searchField, 'filter_from', 'filter_to'))) : ?>
			<div class="js-stools-field-filter">
				<?php echo $field->input; ?>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
	<div class="filter_date">
		<div class="js-stools-field-filter">
			<?php echo $filters['filter_from']->label . $filters['filter_from']->input; ?>
		</div>
		<div class="js-stools-field-filter">
			<?php echo $filters['filter_to']->label . $filters['filter_to']->input; ?>
		</div>
    </div>
<?php endif; ?>
