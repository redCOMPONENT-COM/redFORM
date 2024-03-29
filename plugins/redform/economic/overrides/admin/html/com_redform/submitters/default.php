<?php
/**
 * @package     Redform.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * This is an override for economic integration plugin
 */

defined('_JEXEC') or die;

JHtml::_('rbootstrap.tooltip');
JHtml::_('rjquery.chosen', 'select');

$action = JRoute::_('index.php?option=com_redform&view=submitters');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');

if (!empty($this->items))
{
	$sids = array();

	foreach ($this->items as $item)
	{
		$sids[] = $item->id;
	}

	$invoices = null;

	JPluginHelper::importPlugin('redform');
	$dispatcher = JDispatcher::getInstance();
	$dispatcher->trigger('onGetSubmittersInvoices', array($sids, &$invoices));

	$pdfImg = JHtml::image('plugins/redform/economic/images/pdf.png', 'get pdf');
}
?>

<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

	<?php
	echo RdfLayoutHelper::render(
		'submitters.searchtools.default',
		array(
			'view' => $this,
			'options' => array(
				'filterButton' => true,
				'filtersHidden' => false,
				'searchField' => 'search_submitters',
				'searchFieldSelector' => '#filter_search_submitters',
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
	<table class="table table-striped table-hover" id="submitterList">
		<thead>
		<tr>
			<th width="1%" class="hidden-phone">
				<input type="checkbox" name="checkall-toggle" value=""
				       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
			</th>
			<th width="1%" class="nowrap hidden-phone">
				<?php echo JHtml::_('rsearchtools.sort', 'COM_REDFORM_ID', 's.id', $listDirn, $listOrder); ?>
			</th>
			<th class="nowrap hidden-phone">
				<?php echo JHtml::_('rsearchtools.sort', 'COM_REDFORM_Submission_date', 's.submission_date', $listDirn, $listOrder); ?>
			</th>
			<?php if ($this->formInfo->enable_confirmation): ?>
				<th class="nowrap hidden-phone">
					<?php echo JHtml::_('rsearchtools.sort', 'COM_REDFORM_confirmed_HEADER', 's.confirmed_date', $listDirn, $listOrder); ?>
				</th>
			<?php endif; ?>
			<th class="nowrap hidden-phone">
				<?php echo JHtml::_('rsearchtools.sort', 'COM_REDFORM_Form_name', 'f.formname', $listDirn, $listOrder); ?>
			</th>
			<th class="nowrap hidden-phone">
				<?php echo JText::_('COM_REDFORM_Unique_id'); ?>
			</th>

			<?php if ($this->integration && $this->params->get('showintegration', false)): ?>
				<th class="nowrap hidden-phone">
					<?php echo JText::_('COM_REDFORM_Integration'); ?>
				</th>
			<?php endif; ?>

			<?php foreach ($this->fields as $key => $value): ?>
				<th class="nowrap hidden-phone">
					<?php echo $value->field_header; ?>
				</th>
			<?php endforeach; ?>

			<?php if ($this->formInfo->activatepayment): ?>
				<th class="nowrap hidden-phone">
					<?php echo JText::_('COM_REDFORM_Price'); ?>
				</th>
				<th class="nowrap hidden-phone" width="auto">
					<?php echo JText::_('COM_REDFORM_Payment'); ?>
				</th>
			<?php endif;?>
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
					<?php echo $item->id; ?>
				</td>
				<td>
					<a href="<?php echo JRoute::_('index.php?option=com_redform&task=submitter.edit&id=' . $item->id); ?>">
						<?php echo $this->escape($item->submission_date); ?>
					</a>
				</td>

				<?php if ($this->formInfo->enable_confirmation): ?>
				<td>
					<?php if (RdfHelper::isNonNullDate($item->confirmed_date)): ?>
						<i class="icon-ok hasToolTip" title="<?php
						echo RHtml::tooltipText(JText::sprintf('COM_REDFORM_COMFIRMATION_INFO', $item->confirmed_date, $item->confirmed_type, $item->confirmed_ip));
						?>"/>
					<?php endif; ?>
				</td>
				<?php endif; ?>

				<td>
					<?php echo $this->escape($item->formname); ?>
				</td>
				<td>
					<?php echo $this->escape($item->submit_key); ?>
				</td>

				<?php if ($this->integration && $this->params->get('showintegration', false)): ?>
					<td>
						<?php echo $this->escape($item->integration); ?></td>
					</td>
				<?php endif; ?>

				<?php
				foreach ($this->fields as $key => $field)
				{
					$fieldname = 'field_'. $field->field_id;

					if (isset($item->{$fieldname}))
					{
						$data = str_replace('~~~', '<br />', $item->$fieldname);

						if (stristr($data, JPATH_ROOT))
						{
							$data = '<a href="' . str_replace(JPATH_ROOT, JURI::root(true), $data) . '" target="_blank">' . $data . '</a>';
						}

						echo '<td>' . $data . '</td>';
					}
					else
					{
						echo '<td></td>';
					}
				}
				?>

				<?php if ($this->formInfo->activatepayment): ?>
					<td class="submitters-price"><?php echo $item->price ? RdfHelper::formatPrice($item->price + $item->vat, $item->currency) : ''; ?></td>
					<td class="paymentrequests" width="auto">
						<?php if ($item->paymentrequests): ?>
						<ul class="unstyled">
							<?php foreach ($item->paymentrequests as $pr): ?>
								<li>
									<?php if ($pr->invoice_id): ?>
										<span class="invoice-id"><?= $pr->invoice_id ?></span>
									<?php endif; ?>
									<?php echo RdfHelper::formatPrice($pr->price + $pr->vat, $pr->currency); ?>
									<?php $link = JHTML::link(JRoute::_('index.php?option=com_redform&view=payments&pr=' . $pr->prid), JText::_('COM_REDFORM_history')); ?>
									<?php if (!$pr->paid): ?>
										<span class="hasToolTip" title="<?php echo RHtml::tooltipText(JText::_('COM_REDFORM_REGISTRATION_NOT_PAID'), $pr->status); ?>"><i class="icon-remove"></i><?php echo $link; ?></span>
										<?php echo ' '.JHTML::link(JURI::root().'index.php?option=com_redform&task=payment.select&key=' . $item->submit_key, JText::_('COM_REDFORM_link')); ?>
									<?php else: ?>
										<span class="hasToolTip" title="<?php echo RHtml::tooltipText(JText::_('COM_REDFORM_REGISTRATION_PAID'), $pr->status); ?>"><i class="icon-ok"></i><?php echo $link; ?></span>
									<?php endif; ?>

									<?php if (isset($invoices[$item->id][$pr->prid])): ?>
										<?php foreach ($invoices[$item->id][$pr->prid] as $invoice):
											if (!$invoice->booked):
												$booklink  = 'index.php?option=com_ajax&group=redform&plugin=book&format=raw&id=' . $invoice->id . '&reference=' . $invoice->reference; ?>
												<span class="book-it">
													<?php echo JHtml::link($booklink, JText::_('PLG_REDFORM_ECONOMIC_BOOK')); ?>
												</span>
											<?php else:
												$pdflink  = 'index.php?option=com_ajax&group=redform&plugin=getpdf&format=raw&id=' . $invoice->id . '&reference=' . $invoice->reference;
												$turnlink = 'index.php?option=com_ajax&group=redform&plugin=turninvoice&format=raw&id=' . $invoice->id . '&reference=' . $invoice->reference; ?>
												<div class="invoice">
													<?php echo $invoice->reference
														. ' ' . JHTML::link($pdflink, $pdfImg, array('title' => JText::_('PLG_REDFORM_ECONOMIC_GET_PDF')))
														. ' - '. ($invoice->turned ? JText::_('PLG_REDFORM_ECONOMIC_INVOICE_TURNED').': '.$invoice->turned : JHTML::link($turnlink, JText::_('PLG_REDFORM_ECONOMIC_TURN_INVOICE'))); ?></div>
											<?php endif ;?>
										<?php endforeach; ?>
									<?php endif;?>
								</li>
							<?php endforeach; ?>
						</ul>
						<?php endif;?>
					</td>
				<?php endif;?>
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
		<input type="hidden" name="integration" value="<?php echo $this->integration; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
