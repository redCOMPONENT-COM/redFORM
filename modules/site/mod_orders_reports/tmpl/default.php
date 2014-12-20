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
$doc->addScript(JURI::base() . 'modules/mod_orders_reports/media/js/mod_orders_reports.js');
$doc->addStyleSheet(JURI::base() . 'modules/mod_orders_reports/media/css/mod_orders_reports.css');

JText::script('MOD_ORDERS_STATS_GRAPH_LABEL_DAYOFTHEMONTH');
JText::script('MOD_ORDERS_STATS_GRAPH_LABEL_MONTH');
JText::script('MOD_ORDERS_STATS_GRAPH_LABEL_CANCELLED');
JText::script('MOD_ORDERS_STATS_GRAPH_LABEL_ERRORS');
JText::script('MOD_ORDERS_STATS_GRAPH_LABEL_REPORTS');

$rand = uniqid();
?>
<script type="text/javascript">
	var monthStats = <?php echo json_encode($items['month']); ?>;
	var yearStats = <?php echo json_encode($items['year']); ?>;
</script>

<div role="tabpanel">
	<!-- Nav tabs -->
	<ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active">
			<a href="#area1tab1<?php echo $rand; ?>" aria-controls="home" role="tab" data-toggle="tab">
				<?php echo JText::_('MOD_ORDERS_STATS_CANCELLED_ERRORS_REPORTS'); ?>
			</a>
		</li>
		<li role="presentation">
			<a href="#area1tab2<?php echo $rand; ?>" aria-controls="profile" role="tab" data-toggle="tab">
				<?php echo JText::_('MOD_ORDERS_STATS_MONTHLY_CHART'); ?>
			</a>
		</li>
		<li role="presentation">
			<a href="#area1tab3<?php echo $rand; ?>" aria-controls="messages" role="tab" data-toggle="tab">
				<?php echo JText::_('MOD_ORDERS_STATS_YEARLY_CHART'); ?>
			</a>
		</li>
	</ul>

	<!-- Tab panes -->
	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active" id="area1tab1<?php echo $rand; ?>">
			<table class="table table-striped">
				<thead>
				<tr>
					<th><?php echo JText::_('MOD_ORDERS_STATS_CANCELLED'); ?></th>
					<th><?php echo JText::_('MOD_ORDERS_STATS_ERRORS'); ?></th>
					<th><?php echo JText::_('MOD_ORDERS_STATS_REPORTS'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php $i = 1; ?>
				<?php if ($items && isset($items['today'])): ?>
					<tr>
						<td><?php echo $items['today']->cancelled; ?></td>
						<td><?php echo $items['today']->errors; ?></td>
						<td><?php echo $items['today']->reports; ?></td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<div role="tabpanel" class="tab-pane" id="area1tab2<?php echo $rand; ?>">
			<div id="error_month_div" style="width: 500px; height: 200px;"></div>
		</div>
		<div role="tabpanel" class="tab-pane" id="area1tab3<?php echo $rand; ?>">
			<div id="error_year_div" style="width: 500px; height: 200px;"></div>
		</div>
	</div>
</div>