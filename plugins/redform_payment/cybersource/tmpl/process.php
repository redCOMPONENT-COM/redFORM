<?php
/**
 * @package     Redform
 * @subpackage  Payment.cybersource
 * @copyright   Copyright (C) 2008-2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

extract($data);

$intro = $request->processIntroText;
?>
<h1><?php echo JText::_('PLG_REDFORM_PAYMENT_CYBERSOURCE_PROCESS_FORM_TITLE'); ?></h1>
<form method="post" action="<?php echo $target; ?>">
	<fieldset>

		<div class="payment-intro">
			<?php if ($intro): ?>
				<?php echo $intro; ?>
			<?php else: ?>
				<?php echo $request->title; ?>
			<?php endif; ?>
		</div>

		<p><?php echo JText::_('PLG_REDFORM_PAYMENT_CYBERSOURCE_PRICE'); ?>: <?php echo $price . ' ' . $details->currency; ?></p>
	</fieldset>
	<?php foreach ($post_variables as $key => $value): ?>
		<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>"/>
	<?php endforeach; ?>

	<div class="redform-payment">
		<button type="submit"><?php echo JText::_('PLG_REDFORM_PAYMENT_CYBERSOURCE_FORM_SUBMIT'); ?></button>
	</div>
</form>
