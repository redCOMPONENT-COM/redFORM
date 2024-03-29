<?php
/**
 * @package     Redform.plugins
 * @subpackage  payment
 * @copyright   Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */
defined('_JEXEC') or die;

/**
 * @var   array               $displayData  data
 * @var   RdfCorePaymentCart  $cart         cart
 */
extract($displayData);

$intro = $params->get('introduction');

if ($intro)
{
	$cartEntity = RdfEntityCart::load($cart->id);
	$intro = $cartEntity->replaceTags($intro);
}

if ($params->get('auto_open', 0))
{
	$js = <<<JS
	window.addEvent('domready', function() {
	setTimeout(function() {
		document.id('quickpayform').submit();
	}, 1000);
});
JS;
	JFactory::getDocument()->addScriptDeclaration($js);
}
?>
<div class="redform-payment-quickpay">
	<h3><?= JText::_('PLG_REDFORM_QUICKPAY_PAYMENT_TITLE'); ?></h3>

	<?php if (!empty($intro)): ?>
	<div class="intro">
		<?= $intro ?>
	</div>
	<?php endif; ?>

	<a href="<?= $paymentLink ?>" class="btn btn-primary"><?= JText::_('PLG_REDFORM_QUICKPAY_OPEN_PAYMENT_WINDOW') ?></a>

	<?php if ($return_url): ?>
		<div class="return"><?= JHTML::link($return_url, JText::_('JCancel')) ?></div>
	<?php endif; ?>
</div>
