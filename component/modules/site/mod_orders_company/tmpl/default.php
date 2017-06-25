<?php
/**
 * @package     Redform.Frontend
 * @subpackage  mod_orders_company
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$doc = JFactory::getDocument();
$doc->addScript("https://www.google.com/jsapi");
$doc->addStyleSheet(JURI::base() . 'modules/mod_orders_company/media/css/mod_orders_company.css');
$rand = uniqid();
?>
<script type="text/javascript">
	// Load the Visualization API and the piechart package.
	google.load('visualization', '1.0', {'packages':['corechart']});

	// Set a callback to run when the Google Visualization API is loaded.
	google.setOnLoadCallback(function() {

		// Create the data table.
		var daily = google.visualization.arrayToDataTable([
			['Type', 'Orders'],
			['<?php echo JText::_('MOD_ORDERS_COMPANY_NEW_ORDERS'); ?>', <?php echo $data['today']->total; ?>],
			['<?php echo JText::_('MOD_ORDERS_COMPANY_MISSING_ORDERS'); ?>', <?php echo $data['today']->total > $params->get('goal') ? 0 : $params->get('goal') - $data['today']->total; ?>]
		]);

		// Set chart options
		var options = {'width':400,
			'height':200};

		// Instantiate and draw our chart, passing in some options.
		var chart = new google.visualization.PieChart(document.getElementById('daily_div<?php echo $rand; ?>'));
		chart.draw(daily, options);

		var month = new google.visualization.DataTable();
		month.addColumn('number', '<?php echo JText::_('MOD_ORDERS_COMPANY_DAY'); ?>');
		month.addColumn('number', '<?php echo JText::_('MOD_ORDERS_COMPANY_EL_ORDERS'); ?>');
		month.addColumn('number', '<?php echo JText::_('MOD_ORDERS_COMPANY_GAS_ORDERS'); ?>');
		month.addColumn('number', '<?php echo JText::_('MOD_ORDERS_COMPANY_TOTAL_ORDERS'); ?>');

		var raw = <?php echo json_encode($data['month']); ?>

		var rows = [];

		for (var i = 0, n = raw.length; i < n; i++)
		{
			var row = [raw[i].day, raw[i].elec, raw[i].gas, raw[i].elec + raw[i].gas];
			rows.push(row);
		}

		month.addRows(rows);

		var chart = new google.visualization.LineChart(document.getElementById('month_div<?php echo $rand; ?>'));
		chart.draw(month);
	});

	var monthData = <?php echo json_encode($data['month']); ?>;
</script>

<div role="tabpanel">
	<!-- Nav tabs -->
	<ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active">
			<a href="#area1tab1<?php echo $rand; ?>" aria-controls="home" role="tab" data-toggle="tab">
				<?php echo JText::_('MOD_ORDERS_COMPANY_DAILY_GOAL'); ?>
			</a>
		</li>
		<li role="presentation">
			<a href="#area1tab2<?php echo $rand; ?>" aria-controls="profile" role="tab" data-toggle="tab">
				<?php echo JText::_('MOD_ORDERS_COMPANY_DAILY_CHART'); ?>
			</a>
		</li>
		<li role="presentation">
			<a href="#area1tab3<?php echo $rand; ?>" aria-controls="messages" role="tab" data-toggle="tab">
				<?php echo JText::_('MOD_ORDERS_COMPANY_MONTHLY_SALES'); ?>
			</a>
		</li>
	</ul>

	<!-- Tab panes -->
	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active" id="area1tab1<?php echo $rand; ?>">
			<table class="table table-striped">
				<thead>
				<tr>
					<th><?php echo JText::_('MOD_ORDERS_COMPANY_EL_ORDERS'); ?></th>
					<th><?php echo JText::_('MOD_ORDERS_COMPANY_GAS_ORDERS'); ?></th>
					<th><?php echo JText::_('MOD_ORDERS_COMPANY_TOTAL_ORDERS'); ?></th>
					<th><?php echo JText::_('MOD_ORDERS_COMPANY_GOAL'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php if ($data['today']): ?>
					<tr>
						<td><?php echo $data['today']->elec; ?></td>
						<td><?php echo $data['today']->gas; ?></td>
						<td><?php echo $data['today']->total; ?></td>
						<td><?php echo $params->get('goal'); ?></td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<div role="tabpanel" class="tab-pane" id="area1tab2<?php echo $rand; ?>">
			<div id="daily_div<?php echo $rand; ?>"></div>
		</div>
		<div role="tabpanel" class="tab-pane" id="area1tab3<?php echo $rand; ?>">
			<div id="month_div<?php echo $rand; ?>"></div>
		</div>
	</div>
</div>