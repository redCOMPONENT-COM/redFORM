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

$action = JRoute::_('index.php?option=com_redform&view=fields');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';
?>
<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

	<?php
	echo RLayoutHelper::render(
		'searchtools.default',
		array(
			'view' => $this,
			'options' => array(
				'filterButton' => false,
				'searchField' => 'search_fields',
				'searchFieldSelector' => '#filter_search_fields',
				'limitFieldSelector' => '#list_field_limit',
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
		<table class="table table-striped table-hover" id="fieldList">
			<thead>
			<tr>
				<th width="1%" class="hidden-phone">
					<input type="checkbox" name="checkall-toggle" value=""
					       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th width="1%" class="nowrap center">
					<?php echo JHtml::_('rsearchtools.sort', 'JSTATUS', 'f.published', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap hidden-phone">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDEVENT_Field', 'f.field', $listDirn, $listOrder); ?>
				</th>
				<th width="18%" class="nowrap hidden-phone">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDEVENT_FIELD_HEADER', 'f.field_header', $listDirn, $listOrder); ?>
				</th>
				<th width="18%" class="nowrap hidden-phone">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDEVENT_Type', 'f.fieldtype', $listDirn, $listOrder); ?>
				</th>
				<th width="12%" class="nowrap hidden-phone">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDFORM_Required', 'f.validate', $listDirn, $listOrder); ?>
				</th>
				<th width="12%" class="nowrap hidden-phone">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDFORM_Unique', 'f.unique', $listDirn, $listOrder); ?>
				</th>
				<th width="12%" class="nowrap hidden-phone">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDFORM_Form', 'fo.formname', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap hidden-phone">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDFORM_ID', 'f.id', $listDirn, $listOrder); ?>
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
							<?php echo JHtml::_('rgrid.published', $item->published, $i, 'fields.', $canChange, 'cb'); ?>
						</td>
						<td>
							<?php if ($item->checked_out) : ?>
								<?php echo JHtml::_('rgrid.checkedout', $i, $item->checked_out,
									$item->checked_out_time, 'forms.', $canCheckin); ?>
							<?php endif; ?>
							<a href="<?php echo JRoute::_('index.php?option=com_redform&task=field.edit&id=' . $item->id); ?>">
								<?php echo $this->escape($item->field); ?>
							</a>
						</td>
						<td>
							<?php echo $this->escape($item->field_header); ?>
						</td>
						<td>
							<?php echo $this->escape($item->fieldtype); ?>
						</td>
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
							<?php echo $this->escape($item->formname); ?>
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
