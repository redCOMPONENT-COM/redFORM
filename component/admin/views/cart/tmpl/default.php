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

JHtml::_('behavior.keepalive');
JHTML::_('behavior.formvalidation');

$action = JRoute::_('index.php?option=com_redform&view=cart');

/**
 * @var RdfEntityCart
 */
$cart = $this->cart;
?>
<ul class="nav nav-tabs" id="formTabs">
	<li class="active">
		<a href="#details" data-toggle="tab">
			<?php echo JText::_('COM_REDFORM_DETAILS'); ?>
		</a>
	</li>
	<li>
		<a href="#billing" data-toggle="tab">
			<?php echo JText::_('COM_REDFORM_BILLING_INFO_TITLE'); ?>
		</a>
	</li>
	<li>
		<a href="#payments" data-toggle="tab">
			<?php echo JText::_('COM_REDFORM_PAYMENTS_HISTORY'); ?>
		</a>
	</li>
</ul>
<div class="tab-content">
	<div class="tab-pane active" id="details">
		<?php echo $this->loadTemplate('details'); ?>
	</div>

	<div class="tab-pane" id="billing">
		<?php echo $this->loadTemplate('billing'); ?>
	</div>

	<div class="tab-pane" id="payments">
		<?php echo $this->loadTemplate('payments'); ?>
	</div>
</div>

<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm"
      class="form-validate form-horizontal">

	<!-- hidden fields -->
	<input type="hidden" name="option" value="com_redform">
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
	<input type="hidden" name="task" value="">
	<?php echo JHTML::_('form.token'); ?>
</form>
