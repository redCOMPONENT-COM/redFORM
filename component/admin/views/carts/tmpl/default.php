<?php
/**
 * @package     Redform.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

JHtml::_('rbootstrap.tooltip', '.hasToolTip');
JHtml::_('rjquery.chosen', 'select');

$action = JRoute::_('index.php?option=com_redform&view=carts');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$search = $this->state->get('filter.search');

?>
<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

	<?php
	echo RdfLayoutHelper::render(
		'searchtools.default',
		array(
			'view' => $this,
			'options' => array(
				'filterButton' => true,
				'filtersHidden' => empty($this->activeFilters),
				'searchField' => 'search_carts',
				'searchFieldSelector' => '#filter_search_carts',
				'limitFieldSelector' => '#list_cart_limit',
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
		<table class="table table-striped table-hover" id="cartList">
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
					<?php echo Text::_('JDATE'); ?>
				</th>
				<th class="nowrap hidden-phone">
					<?php echo Text::_('COM_REDFORM_INVOICE_ID'); ?>
				</th>
				<th class="nowrap hidden-phone">
					<?php echo Text::_('COM_REDFORM_PRICE'); ?>
				</th>
				<th class="nowrap hidden-phone">
					<?php echo Text::_('COM_REDFORM_PAYMENTCURRENCY'); ?>
				</th>
				<th class="nowrap hidden-phone">
					<?php echo Text::_('COM_REDFORM_PAID'); ?>
				</th>
				<th width="1%" class="nowrap hidden-phone">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDFORM_ID', 'obj.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<?php if ($this->items): ?>
				<tbody>
				<?php foreach ($this->items as $i => $item): ?>
					<tr>
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td>
							<?php echo JHtml::_('rgrid.id', $i, $item->id); ?>
						</td>
						<td>
							<a href="<?php echo JRoute::_('index.php?option=com_redform&view=cart&id=' . $item->id); ?>">
								<?php echo $this->escape($item->created); ?>
							</a>
						</td>
						<td>
							<?php echo $item->invoice_id; ?>
						</td>
						<td>
							<?php echo $item->price; ?>
						</td>
						<td>
							<?php echo $item->currency; ?>
						</td>
						<td>
							<?php echo $item->paid; ?>
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
