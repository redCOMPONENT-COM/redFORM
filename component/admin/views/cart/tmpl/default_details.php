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

/**
 * @var RdfEntityCart
 */
$cart = $this->cart;
?>
<dl class="dl-horizontal">
	<dt><?php echo JText::_('COM_REDFORM_INVOICE_ID'); ?></dt>
	<dd><?php echo $this->item->invoice_id; ?></dd>
</dl>
<dl class="dl-horizontal">
	<dt><?php echo JText::_('JDATE'); ?></dt>
	<dd><?php echo $this->item->created; ?></dd>
</dl>
<dl class="dl-horizontal">
	<dt><?php echo JText::_('COM_REDFORM_PRICE'); ?></dt>
	<dd><?php echo $this->item->currency . ' ' . $this->item->price; ?></dd>
</dl>
<dl class="dl-horizontal">
	<dt><?php echo JText::_('COM_REDFORM_CART_VAT'); ?></dt>
	<dd><?php echo $this->item->currency . ' ' . $this->item->vat; ?></dd>
</dl>

<div class="cart__items">
	<table class="table table-striped table-hover" id="cartList">
		<thead>
		<tr>
			<th width="10" align="center">
				<?php echo '#'; ?>
			</th>
			<th>
				<?php echo Text::_('COM_REDFORM_CART_PAYMENT_REQUEST_ID'); ?>
			</th>
			<th>
				<?php echo Text::_('COM_REDFORM_SUBMISSION_ID'); ?>
			</th>
			<th class="nowrap">SKU</th>
			<th class="nowrap">
				<?php echo Text::_('COM_REDFORM_FIELD_LABEL_LABEL'); ?>
			</th>
			<th class="nowrap">
				<?php echo Text::_('COM_REDFORM_PRICE'); ?>
			</th>
			<th class="nowrap">
				<?php echo Text::_('COM_REDFORM_CART_VAT'); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($cart->getPaymentRequests() as $pr): ?>
			<?php foreach ($pr->getItems() as $pri): ?>
				<tr>
					<td width="10" align="center"><?php echo $pri->id; ?></td>
					<td align="center"><?php echo $pr->id; ?></td>
					<td align="center"><?php echo $pr->submission_id; ?></td>
					<td><?php echo $pri->sku; ?></td>
					<td><?php echo $pri->label; ?></td>
					<td><?php echo $pri->price; ?></td>
					<td><?php echo $pri->vat; ?></td>
				</tr>
			<?php endforeach; ?>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>