<?php
/**
 * @package     RedEVENT.Frontend
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

extract($displayData);

$pdfImg = JHtml::image('plugins/redform/pdfinvoice/images/pdf.png', 'get pdf');
?>
<?php if (!empty($row->paymentRequests)): ?>
	<?php foreach ($row->paymentRequests as $paymentRequest):
		if (!empty($invoices[$row->sid][$paymentRequest->id])):
			foreach ($invoices[$row->sid][$paymentRequest->id] as $invoice):
				$pdflink  = 'index.php?option=com_ajax&group=redform&plugin=getpdf&format=raw&id=' . $invoice->id . '&reference=' . $invoice->reference;
				if ($invoice->booked): ?>
					<div class="invoice">
						<?php echo $invoice->reference
							. ' ' . JHTML::link($pdflink, $pdfImg, array('title' => JText::_('PLG_REDFORM_PDFINVOICE_GET_PDF'))); ?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif ;?>
	<?php endforeach; ?>
<?php endif; ?>
