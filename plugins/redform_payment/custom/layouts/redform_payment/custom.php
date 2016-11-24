<?php
/**
 * @package     Redform.plugins
 * @subpackage  payment
 * @copyright   Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */
defined('_JEXEC') or die;

extract($displayData);

$cart = $request->getCart();
?>
<div class="redform-payment-custom">
	<div class="intro">
		<?= $intro ?>
	</div>

	<form class="custom-payment" method="post" action="<?= $action ?>">
		<dl>
			<dt><?= JText::_('PLG_REFORM_PAYMENT_CUSTOM_PAYMENT_TITLE') ?></dt><dd><?= $request->title ?></dd>
			<dt><?= JText::_('PLG_REFORM_PAYMENT_CUSTOM_PAYMENT_UNIQUE_ID') ?></dt><dd><?= $request->uniqueid ?></dd>
			<dt><?= JText::_('PLG_REFORM_PAYMENT_CUSTOM_PAYMENT_AMOUNT') ?></dt><dd><?= RHelperCurrency::getFormattedPrice($cart->price + $cart->vat, $request->currency) ?></dd>
		</dl>

		<button type="submit"><?= $params->get('confirmButtonLabel', 'Confirm') ?></button>
	</form>

	<?php if ($return_url): ?>
		<div class="return"><?= JHTML::link($return_url, JText::_('Return')) ?></div>
	<?php endif; ?>
</div>
