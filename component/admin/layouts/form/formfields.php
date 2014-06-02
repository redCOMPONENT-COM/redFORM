<?php
/**
 * @package     Redshopb.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$data = $displayData;

$state = $data['state'];
$items = $data['items'];
$pagination = $data['pagination'];
$filterForm = $displayData['filter_form'];
$formName = $data['formName'];
$showToolbar = isset($data['showToolbar']) ? $data['showToolbar'] : false;
$return = isset($data['return']) ? $data['return'] : null;
$action = 'index.php?option=com_redform&view=formfields';

// Allow to override the form action
if (isset($data['action']))
{
	$action = $data['action'];
}

JHtml::_('behavior.keepalive');
JHtml::_('rdropdown.init');
JHtml::_('rbootstrap.tooltip');
JHtml::_('rjquery.chosen', 'select');

$formId = JFactory::getApplication()->input->getInt('id');
$listOrder = $state->get('list.ordering');
$listDirn = $state->get('list.direction');

$saveOrder = ($listOrder == 'ff.ordering' && strtolower($listDirn) == 'asc');

// Items ordering
$ordering = array();

if ($items)
{
	foreach ($items as $item)
	{
		$ordering[0][] = $item->id;
	}
}

// Form filter will not enable search tools
if (isset($data['activeFilters']['form_id']))
{
	unset($data['activeFilters']['form_id']);
}

$search = $state->get('filter.search');

$searchToolsOptions = array(
	"searchFieldSelector" => "#filter_search_fields",
	"orderFieldSelector" => "#list_fullordering",
	"searchField" => "search_fields",
	"limitFieldSelector" => "#list_field_limit",
	"activeOrder" => $listOrder,
	"activeDirection" => $listDirn,
	"formSelector" => ("#" . $formName),
	"filtersHidden" => (bool) empty($data['activeFilters'])
);
?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			$('#<?php echo $formName; ?>').searchtools(
				<?php echo json_encode($searchToolsOptions); ?>
			);
		});
	})(jQuery);
</script>
<form action="<?php echo $action; ?>" name="<?php echo $formName; ?>" class="adminForm" id="<?php echo $formName; ?>"
      method="post">
	<div class="panel panel-default">
		<div class="panel-heading">
		<?php
		// Render the toolbar?
		if ($showToolbar)
		{
			echo RdfHelperLayout::render('formfields.toolbar', $data);
		}
		?>
		</div>

		<div class="panel-body">
		<?php
		echo RdfHelperLayout::render(
			'searchtools.default',
			array(
				'view' => (object) array(
						'filterForm' => $data['filter_form'],
						'activeFilters' => $data['activeFilters']
					),
				'options' => $searchToolsOptions
			)
		);
		?>

		<?php if (empty($items)) : ?>
			<div class="alert alert-info">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<div class="pagination-centered">
					<h3><?php echo JText::_('COM_REDFORM_NOTHING_TO_DISPLAY') ?></h3>
				</div>
			</div>
		<?php else : ?>
			<table class="table table-striped table-hover footable" id="fieldList">
				<thead>
				<tr>
					<th width="1%">
						<input type="checkbox" name="checkall-toggle" value=""
						       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
					</th>
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('rsearchtools.sort', 'JSTATUS', 'f.published', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap" data-toggle="true">
						<?php echo JHtml::_('rsearchtools.sort', 'COM_REDFORM_FIELD', 'f.field', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
					<?php if ($search == ''): ?>
						<th width="20" class="nowrap">
							<?php echo JHTML::_('rsearchtools.sort', 'COM_REDFORM_ORDERING', 'ff.ordering', $listDirn, $listOrder); ?>
						</th>
					<?php endif; ?>
					<th width="12%" class="nowrap hidden-phone">
						<?php echo JHtml::_('rsearchtools.sort', 'COM_REDFORM_Required', 'ff.validate', $listDirn, $listOrder); ?>
					</th>
					<th width="12%" class="nowrap hidden-phone">
						<?php echo JHtml::_('rsearchtools.sort', 'COM_REDFORM_Unique', 'ff.unique', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap" data-hide="phone">
						<?php echo JHtml::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'ff.id', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
					</th>
				</tr>
				</thead>
				<?php if ($items): ?>
					<tbody>
					<?php foreach ($items as $i => $item): ?>
						<?php
						$canChange = 1;
						$canEdit = 1;
						$canCheckin = 1;
						$orderkey = array_search($item->id, $ordering[0]);
						?>
						<tr>
							<td>
								<?php echo JHtml::_('rgrid.id', $i, $item->id, false, 'cid', $formName); ?>
							</td>
							<td>
								<?php echo JHtml::_('rgrid.published', $item->published, $i, 'formfields.', $canChange, 'cb', null, null, $formName); ?>
							</td>
							<td>
								<?php
								$itemUrl = 'index.php?option=com_redform&task=formfield.edit&id=' . $item->id
									. '&jform[form_id]=' . $formId . '&from_form=1';

								if ($return)
								{
									$itemUrl .= '&return=' . $return;
								}
								?>
								<a href="<?php echo $itemUrl; ?>">
									<?php echo $item->field; ?>
								</a>
							</td>

							<?php if ($search == ''): ?>
								<td class="order nowrap center">
									<span class="sortable-handler hasTooltip <?php echo ($saveOrder) ? '' : 'inactive' ;?>"
									      title="<?php echo ($saveOrder) ? '' :JText::_('COM_REDITEM_ORDERING_DISABLED');?>">
										<i class="icon-move"></i>
									</span>
									<input type="text" style="display:none" name="order[]" value="<?php echo $orderkey + 1;?>" class="text-area-order" />
								</td>
							<?php endif; ?>

							<td>
								<?php echo $item->validate ?
									JHTML::_('image', 'admin/tick.png', JText::_('JYES'), null, true) :
									JHTML::_('image', 'admin/publish_x.png', JText::_('JNO'), null, true); ?>
							</td>

							<td>
								<?php echo $item->unique ?
									JHTML::_('image', 'admin/tick.png', JText::_('JYES'), null, true) :
									JHTML::_('image', 'admin/publish_x.png', JText::_('JNO'), null, true); ?>
							</td>

							<td>
								<?php echo $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				<?php endif; ?>
			</table>
			<?php echo $pagination->getPaginationLinks('pagination.links', array('showLimitBox' => false)); ?>
		<?php endif; ?>

		<div>
			<input type="hidden" name="task" value="form.saveModelState">
			<?php if ($return) : ?>
				<input type="hidden" name="return" value="<?php echo $return ?>">
			<?php endif; ?>
			<input type="hidden" name="jform[form_id]" value="<?php echo $formId ?>">
			<input type="hidden" name="boxchecked" value="0">
			<input type="hidden" name="from_form" value="1">
			<?php echo JHtml::_('form.token'); ?>
		</div>

		</div>
	</div>
</form>

