<?php
/**
 * @package     Redevent
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * This is an override for economic integration plugin
 */

defined('JPATH_REDCORE') or die;

$row = $displayData;

$link = JHTML::link(JRoute::_('index.php?option=com_redform&view=payments&submit_key=' . $row->submit_key), JText::_('COM_REDEVENT_history'));


$invoices = null;

JPluginHelper::importPlugin('redform');
$dispatcher = JDispatcher::getInstance();
$dispatcher->trigger('onGetSubmittersInvoices', array(array($row->sid), &$invoices));

$pdfImg = JHtml::image('plugins/redform/economic/images/pdf.png', 'get pdf');

$uri = JFactory::getURI();
$return = '&return=' . base64_encode($uri->toString());
?>
<?php if ($row->paymentRequests): ?>
	<ul class="paymentrequest unstyled">
		<?php foreach ($row->paymentRequests as $pr): ?>
		<li>
			<?php echo RdfHelper::formatPrice($pr->price + $pr->vat, $pr->currency); ?>
			<?php $link = JHTML::link(JRoute::_('index.php?option=com_redform&view=payments&pr=' . $pr->id . $return), JText::_('COM_REDEVENT_history')); ?>
			<?php if (!$pr->paid): ?>
				<span class="hasTip" title="<?php echo JText::_('COM_REDEVENT_REGISTRATION_NOT_PAID'); ?>"><i class="icon-remove"></i><?php echo $link; ?></span>
				<?php echo ' '.JHTML::link(JURI::root().'index.php?option=com_redform&task=payment.select&key=' . $row->submit_key . $return, JText::_('COM_REDEVENT_link')); ?>
			<?php else: ?>
				<span class="hasTip" title="<?php echo JText::_('COM_REDEVENT_REGISTRATION_PAID'); ?>"><i class="icon-ok"></i><?php echo $link; ?></span>
			<?php endif; ?>

			<?php if (isset($invoices[$row->sid][$pr->id])): ?>
				<?php foreach ($invoices[$row->sid][$pr->id] as $invoice):
					if (!$invoice->booked):
						$booklink  = 'index.php?option=com_ajax&group=redform&plugin=book&format=raw&id=' . $invoice->id . '&reference=' . $invoice->reference . $return; ?>
						<span class="book-it">
													<?php echo JHtml::link($booklink, JText::_('PLG_REDFORM_ECONOMIC_BOOK')); ?>
												</span>
					<?php else:
						$pdflink  = 'index.php?option=com_ajax&group=redform&plugin=getpdf&format=raw&id=' . $invoice->id . '&reference=' . $invoice->reference;
						$turnlink = 'index.php?option=com_ajax&group=redform&plugin=turninvoice&format=raw&id=' . $invoice->id . '&reference=' . $invoice->reference . $return; ?>
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
<?php endif; ?>
