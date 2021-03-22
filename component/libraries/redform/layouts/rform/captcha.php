<?php
/**
 * @package     Redform.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
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
