<?php
/**
 * @package     Redform.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

extract($displayData);
?>
<div class="fieldline">
	<div class="label">
		<label><?= JText::_('COM_REDFORM_CAPTCHA_LABEL') ?></label>
	</div>
	<div id="redformcaptcha"><?= $captcha_html ?></div>
</div>
