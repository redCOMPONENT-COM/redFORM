<?php
/**
 * @package     Redform.Frontend
 * @subpackage  mod_orders_stats
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$doc = JFactory::getDocument();
$doc->addScript("https://www.google.com/jsapi");
$doc->addScript(JURI::base() . 'modules/mod_orders_stats/media/js/mod_orders_stats.js');
$doc->addStyleSheet(JURI::base() . 'modules/mod_orders_stats/media/css/mod_orders_stats.css');
?>
<script type="text/javascript">
	var companySales = <?php echo json_encode($items->companySales); ?>;
</script>

<div role="tabpanel">
	<!-- Nav tabs -->
	<ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active">
			<a href="#area1tab1" aria-controls="home" role="tab" data-toggle="tab">
				<?php echo JText::_('MOD_ORDERS_STATS_TOP_SALESMEN'); ?>
			</a>
		</li>
		<li role="presentation">
			<a href="#area1tab2" aria-controls="profile" role="tab" data-toggle="tab">
				<?php echo JText::_('MOD_ORDERS_STATS_COMPANIES'); ?>
			</a>
		</li>
		<li role="presentation">
			<a href="#area1tab3" aria-controls="messages" role="tab" data-toggle="tab">
				<?php echo JText::_('MOD_ORDERS_STATS_CHART'); ?>
			</a>
		</li>
	</ul>

	<!-- Tab panes -->
	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active" id="area1tab1">
			<table class="table table-striped">
				<thead>
				<tr>
					<th colspan="2"><?php echo JText::_('MOD_ORDERS_STATS_SALESMAN'); ?></th>
					<th><?php echo JText::_('MOD_ORDERS_STATS_COMPANY'); ?></th>
					<th><?php echo JText::_('MOD_ORDERS_STATS_EL_ORDERS'); ?></th>
					<th><?php echo JText::_('MOD_ORDERS_STATS_GAS_ORDERS'); ?></th>
					<th><?php echo JText::_('MOD_ORDERS_STATS_TOTAL_ORDERS'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php $i = 1; ?>
				<?php foreach ($items->topSales as $row): ?>
					<tr>
						<td><?php echo $i++; ?></td>
						<td><?php echo $row->name; ?></td>
						<td><?php echo $row->company; ?></td>
						<td><?php echo $row->elec; ?></td>
						<td><?php echo $row->gas; ?></td>
						<td><?php echo $row->total; ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div role="tabpanel" class="tab-pane" id="area1tab2">
			<table class="table table-striped">
				<thead>
				<tr>
					<th colspan="2"><?php echo JText::_('MOD_ORDERS_STATS_COMPANY'); ?></th>
					<th><?php echo JText::_('MOD_ORDERS_STATS_EL_ORDERS'); ?></th>
					<th><?php echo JText::_('MOD_ORDERS_STATS_GAS_ORDERS'); ?></th>
					<th><?php echo JText::_('MOD_ORDERS_STATS_TOTAL_ORDERS'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php $i = 1; ?>
				<?php foreach ($items->companySales as $row): ?>
					<tr>
						<td><?php echo $i++; ?></td>
						<td><?php echo $row->name; ?></td>
						<td><?php echo $row->elec; ?></td>
						<td><?php echo $row->gas; ?></td>
						<td><?php echo $row->total; ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div role="tabpanel" class="tab-pane" id="area1tab3">
			<div id="sales_div"></div>
		</div>
	</div>
</div>