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

$action = JRoute::_('index.php?option=com_redform&view=payments');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
?>
<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
	<?php if (empty($this->items)) : ?>
		<div class="alert alert-info">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<div class="pagination-centered">
				<h3><?php echo JText::_('COM_REDFORM_NOTHING_TO_DISPLAY') ?></h3>
			</div>
		</div>
	<?php else : ?>
		<table class="table table-striped table-hover" id="paymentList">
			<thead>
			<tr>
				<th width="1%" class="hidden-phone">
					<input type="checkbox" name="checkall-toggle" value=""
					       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th width="20%" class="nowrap center">
					<?php echo JText::_('COM_REDFORM_DATE'); ?>
				</th>
				<th width="20%" class="nowrap">
					<?php echo JText::_('COM_REDFORM_Gateway'); ?>
				</th>
				<th width="15%" class="nowrap hidden-phone">
					<?php echo JText::_('COM_REDFORM_Status'); ?>
				</th>
				<th class="nowrap hidden-phone">
					<?php echo JText::_('COM_REDFORM_Info'); ?>
				</th>
				<th width="15%" class="nowrap">
					<?php echo JText::_('COM_REDFORM_Paid'); ?>
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
							<a href="<?php echo JRoute::_('index.php?option=com_redform&task=payment.edit&id=' . $item->id); ?>">
								<?php echo $this->escape($item->date); ?>
							</a>
						</td>
						<td>
							<?php echo $this->escape($item->gateway); ?>
						</td>
						<td>
							<?php echo $this->escape($item->status); ?>
						</td>
						<td>
							<?php echo str_replace("\n", "<br />",$item->data); ?>
						</td>
						<td>
							<?php echo $item->paid; ?>
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
		<input type="hidden" name="submit_key" value="<?php echo $this->state->get('submit_key'); ?>" />
		<input type="hidden" name="return" value="<?php echo base64_encode('index.php?option=com_redform&view=submitters'); ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
