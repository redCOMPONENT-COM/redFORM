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

$select = JHtml::_('select.genericlist', $options, 'gw');
?>
<div class="fieldline gateway-select">
	<div class="label"><?= JText::_('COM_REDFORM_SELECT_PAYMENT_METHOD') ?></div>
	<div class="field"><?= $select ?></div>
</div>
