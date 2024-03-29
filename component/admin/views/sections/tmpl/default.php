<?php
/**
 * @package     Redform.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

JHtml::_('rbootstrap.tooltip', '.hasToolTip');
JHtml::_('rjquery.chosen', 'select');

$action = JRoute::_('index.php?option=com_redform&view=sections');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$saveOrder = ($listOrder == 'obj.ordering' && strtolower($listDirn) == 'asc');
$search = $this->state->get('filter.search');
$ordering = ($listOrder == 'obj.ordering');

if ($saveOrder)
{
	$tableSortLink = 'index.php?option=com_redform&task=sections.saveOrderAjax&tmpl=component';
	JHTML::_('rsortablelist.sortable', 'sectionList', 'adminForm', strtolower($listDirn), $tableSortLink, true, true);
}
?>
<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

	<?php
	echo RdfLayoutHelper::render(
		'searchtools.default',
		array(
			'view' => $this,
			'options' => array(
				'filterButton' => false,
				'filtersHidden' => false,
				'searchField' => 'search_sections',
				'searchFieldSelector' => '#filter_search_sections',
				'limitFieldSelector' => '#list_section_limit',
				'activeOrder' => $listOrder,
				'activeDirection' => $listDirn
			)
		)
	);
	?>

	<hr/>
	<?php if (empty($this->items)) : ?>
		<div class="alert alert-info">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<div class="pagination-centered">
				<h3><?php echo JText::_('COM_REDFORM_NOTHING_TO_DISPLAY') ?></h3>
			</div>
		</div>
	<?php else : ?>
		<table class="table table-striped table-hover" id="sectionList">
			<thead>
			<tr>
				<th width="10" align="center">
					<?php echo '#'; ?>
				</th>
				<th width="1%" class="hidden-phone">
					<input type="checkbox" name="checkall-toggle" value=""
					       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<?php if (($search == '') && ($this->canEditState)) : ?>
					<th width="40">
						<?php echo JHTML::_('rsearchtools.sort', '<i class=\'icon-sort\'></i>', 'obj.ordering', $listDirn, $listOrder); ?>
					</th>
				<?php endif; ?>
				<th class="nowrap hidden-phone">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDFORM_FORMS_XML_FIELD_NAME', 'obj.section', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap hidden-phone">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDFORM_ID', 'obj.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<?php if ($this->items): ?>
				<tbody>
				<?php foreach ($this->items as $i => $item): ?>
					<?php
					$canChange = 1;
					$canEdit = 1;
					$canCheckin = 1;
					$orderkey = array_search($item->id, $this->ordering[0]);
					?>
					<tr>
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td>
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<?php if (($search == '') && ($this->canEditState)) : ?>
							<td class="order nowrap center">
						<span class="sortable-handler hasTooltip <?php echo ($saveOrder) ? '' : 'inactive'; ?>">
							<i class="icon-move"></i>
						</span>
								<input type="text" style="display:none" name="order[]" value="<?php echo $orderkey + 1;?>" class="text-area-order" />
							</td>
						<?php endif; ?>
						<td>
							<?php if ($item->checked_out) : ?>
								<?php echo JHtml::_('rgrid.checkedout', $i, $item->checked_out,
									$item->checked_out_time, 'sections.', $canCheckin); ?>
							<?php endif; ?>
							<a href="<?php echo JRoute::_('index.php?option=com_redform&task=section.edit&id=' . $item->id); ?>">
								<?php echo $this->escape($item->name); ?>
							</a>
						</td>
						<td>
							<?php echo $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			<?php endif; ?>
		</table>
		<?php echo $this->pagination->getPaginationLinks(null, array('showLimitBox' => false)); ?>
	<?php endif; ?>

	<div>
		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
