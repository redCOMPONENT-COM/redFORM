<?php
/**
 * @package     Redform.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

JHtml::_('rbootstrap.tooltip');
JHtml::_('rjquery.chosen', 'select');

$action = JRoute::_('index.php?option=com_redform&view=forms');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';
?>
<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

	<?php
	echo RdfHelperLayout::render(
		'searchtools.default',
		array(
			'view' => $this,
			'options' => array(
				'filterButton' => false,
				'searchField' => 'search_forms',
				'searchFieldSelector' => '#filter_search_forms',
				'limitFieldSelector' => '#list_form_limit',
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
		<table class="table table-striped table-hover" id="categoryList">
			<thead>
			<tr>
				<th width="1%" class="hidden-phone">
					<input type="checkbox" name="checkall-toggle" value=""
					       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th width="1%" class="nowrap center">
					<?php echo JHtml::_('rsearchtools.sort', 'JSTATUS', 'f.published', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('rsearchtools.sort', 'JGLOBAL_TITLE', 'f.formname', $listDirn, $listOrder); ?>
				</th>
				<th width="18%" class="nowrap hidden-phone">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDFORM_FORM_START_DATE', 'f.startdate', $listDirn, $listOrder); ?>
				</th>
				<th width="18%" class="nowrap hidden-phone">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDFORM_FORM_END_DATE', 'f.enddate', $listDirn, $listOrder); ?>
				</th>
				<th width="12%" class="nowrap hidden-phone">
					<?php echo Jtext::_('COM_REDFORM_ACTIVE'); ?>
				</th>
				<th width="18%" class="nowrap hidden-phone">
					<?php echo JText::_('COM_REDFORM_SUBMITTERS'); ?>
				</th>
				<th width="18%" class="nowrap hidden-phone">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDFORM_TAG', 'f.id', $listDirn, $listOrder); ?>
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
					?>
					<tr>
						<td>
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td>
							<?php echo JHtml::_('rgrid.published', $item->published, $i, 'forms.', $canChange, 'cb'); ?>
						</td>
						<td>
							<?php if ($item->checked_out) : ?>
								<?php echo JHtml::_('rgrid.checkedout', $i, $item->checked_out,
									$item->checked_out_time, 'forms.', $canCheckin); ?>
							<?php endif; ?>
							<a href="<?php echo JRoute::_('index.php?option=com_redform&task=form.edit&id=' . $item->id); ?>">
								<?php echo $this->escape($item->formname); ?>
							</a>
						</td>
						<td>
							<?php
							if (RdfHelper::isNonNullDate($item->startdate))
							{
								$date = JFactory::getDate($item->startdate);
								echo $date->toSql();
							}
							?>
						</td>
						<td>
							<?php
							if ($item->formexpires && RdfHelper::isNonNullDate($item->enddate))
							{
								$date = JFactory::getDate($item->enddate);
								echo $date->toSql();
							}
							?>
						</td>
						<td>
							<?php echo $item->formstarted ?
								JHTML::_('image', 'admin/tick.png', JText::_('JYES'), null, true) :
								JHTML::_('image', 'admin/publish_x.png', JText::_('JNO'), null, true); ?>
						</td>
						<td>
							<?php echo JHtml::link('index.php?option=com_redform&view=submitters&filter[form_id]=' . $item->id, JText::_('COM_REDFORM_SUBMITTERS')); ?>
						</td>
						<td>
							<?php echo '{redform}' . $item->id . '{/redform}'; ?>
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
