<?php
/**
 * @package     Redform
 * @subpackage  Payment.stripe
 * @copyright   Copyright (C) 2008-2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

$action = $displayData['action'];
$details = $displayData['details'];
$params = $displayData['params'];
$price = $displayData['price'];
$request = $displayData['request'];
?>
<h3><?php echo $request->title; ?></h3>
<form action="<?php echo $action; ?>" method="POST">
	<script
		src="https://checkout.stripe.com/checkout.js" class="stripe-button"
		data-key="<?php echo $params->get('publishableKey'); ?>"
		data-amount="<?php echo round($price * 100); ?>"
		data-currency="<?php echo $details->currency; ?>"
		data-name="<?php echo $params->get('paymentHeader', 'Test payment'); ?>"
		data-description="<?php echo $request->title; ?>"
		data-image="<?php echo $params->get('imagePath', 'plugins/redform_payment/stripe/128.png'); ?>">
	</script>
	<input type="hidden" name="submitKey" value="<?php echo $request->key; ?>"/>
</form>
