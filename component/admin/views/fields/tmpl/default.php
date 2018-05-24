<?php
/**
 * @package     Redform.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

JHtml::_('rbootstrap.tooltip', '.hasToolTip');
JHtml::_('rjquery.chosen', 'select');

$action = JRoute::_('index.php?option=com_redform&view=fields');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$saveOrder = ($listOrder == 'f.ordering' && strtolower($listDirn) == 'asc');
$search = $this->state->get('filter.search');
$ordering = ($listOrder == 'f.ordering');

if ($saveOrder)
{
	$tableSortLink = 'index.php?option=com_redform&task=fields.saveOrderAjax&tmpl=component';
	JHTML::_('rsortablelist.sortable', 'fieldList', 'adminForm', strtolower($listDirn), $tableSortLink, true, true);
}
?>
<script type="text/javascript">
    Joomla.submitbutton = function (pressbutton)
    {
        var form = document.adminForm;

        if (pressbutton)
        {
            form.task.value = pressbutton;
        }

        if (pressbutton == 'fields.delete')
        {
            if (confirm('<?php echo JText::_("COM_REDFORM_FIELDS_DELETE_WARNING")?>') == true) {
                form.submit();
            }
            else {
                return false;
            }
        }

        form.submit();
    }
</script>
<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

	<?php
	echo RdfLayoutHelper::render(
		'searchtools.default',
		array(
			'view' => $this,
			'options' => array(
				'filterButton' => true,
				'filtersHidden' => false,
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
				<th width="10" align="center">
					<?php echo '#'; ?>
				</th>
				<th width="1%" class="hidden-phone">
					<input type="checkbox" name="checkall-toggle" value=""
					       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th class="nowrap hidden-phone">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDFORM_Field', 'f.field', $listDirn, $listOrder); ?>
				</th>
				<th width="18%" class="nowrap hidden-phone">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDFORM_FIELD_HEADER', 'f.field_header', $listDirn, $listOrder); ?>
				</th>
				<th width="18%" class="nowrap hidden-phone">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDFORM_Type', 'f.fieldtype', $listDirn, $listOrder); ?>
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
					$orderkey = array_search($item->id, $this->ordering[0]);
					?>
					<tr>
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td>
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td>
							<?php if ($item->checked_out) : ?>
								<?php echo JHtml::_('rgrid.checkedout', $i, $item->checked_out,
									$item->checked_out_time, 'fields.', $canCheckin); ?>
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
