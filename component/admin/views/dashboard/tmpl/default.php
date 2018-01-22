<?php
/**
 * @package     Redform.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2008 - 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */


defined('_JEXEC') or die;
?>

<div class="row-fluid">
	<?php foreach (JModuleHelper::getModules('redform_dashboard_widget') as $module) : ?>
		<div class="col-md-3 col-sm-6 col-xs-12">
			<?= JModuleHelper::renderModule($module) ?>
		</div>
	<?php endforeach ?>
</div>

<div class="row-fluid">
	<?php foreach (JModuleHelper::getModules('redform_dashboard_chart') as $module) : ?>
		<div class="col-xs-12 col-md-6">
			<?= JModuleHelper::renderModule($module, ['hideGrid' => true, 'showViewAllBtn' => true]) ?>
		</div>
	<?php endforeach ?>
</div>

<?php foreach (JModuleHelper::getModules('redform_dashboard_bottom') as $module) : ?>
	<?= JModuleHelper::renderModule($module) ?>
<?php endforeach ?>
