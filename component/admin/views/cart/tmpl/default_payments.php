<?php
/**
 * @package     Redform.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

$action = JRoute::_('index.php?option=com_redform&view=cart');

/**
 * @var RdfEntityCart
 */
$cart = $this->cart;
$payments = $cart->getPayments();
?>
<div class="cart__payments">
	<table class="table table-striped table-hover" id="paymentList">
		<thead>
		<tr>
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
				<?php echo JText::_('COM_REDFORM_PAYMENT_DATA'); ?>
			</th>
			<th width="15%" class="nowrap">
				<?php echo JText::_('COM_REDFORM_Paid'); ?>
			</th>
		</tr>
		</thead>
		<?php if (!empty($payments)): ?>
			<tbody>
			<?php foreach ($payments as $i => $item): ?>
				<tr>
					<td>
						<?php echo $this->escape($item->date); ?>
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
	<div class="cart__payments__button">
		<a href="index.php?option=com_redform&view=payment&layout=edit&cart_id=<?= $this->cart->id
		?>&return=<?= base64_encode('index.php?option=com_redform&view=cart&id=' . $this->cart->id) ?>"
			class="btn btn-primary"
		><?= Text::_('JNEW') ?></a>
	</div>
</div>