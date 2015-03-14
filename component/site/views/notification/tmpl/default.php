<?php
/**
 * @package    Redform.Site
 *
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

?>
<div class="redform-confirm">
	<?php if ($this->updated): ?>
		<div class="alert alert-success">
			<?php echo JText::_('COM_REDFORM_VIEW_COMFIRM_SUBMISSION_CONFIRMED'); ?>
		</div>
	<?php else: ?>
		<div class="alert alert-error">
			<?php echo JText::_('COM_REDFORM_VIEW_COMFIRM_SUBMISSION_CONFIRMED_FAILED'); ?>
		</div>
	<?php endif; ?>
</div>
