<?php
/**
 * @package     Redform.Site
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$data = $displayData;
$newsletters = $data->getParam('listname');
?>

<?php if ($data->getParam('force_mailing_list', 0)): ?>

	<?php foreach ($newsletters as $listname): ?>
		<?php if ($listname): ?>
			<input type="hidden" name="<?php echo $data->getFormListElementName(); ?>" value="<?php echo $listname; ?>" />
		<?php endif; ?>
	<?php endforeach; ?>

<?php else: ?>

	<div class="newsletterfields">
		<div id="signuptitle"><?php echo JText::_('COM_REDFORM_SIGN_UP_MAILINGLIST'); ?></div>
		<div class="fieldemail_listnames">

			<?php foreach ($newsletters AS $listkey => $listname): ?>
				<?php if ($listname): ?>
					<div class="nl_<?php echo $listkey; ?>">
						<input type="checkbox" name="<?php echo $data->getFormListElementName(); ?>" value="<?php echo $listname; ?>" />
						<?php echo $listname; ?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>

<?php endif; ?>
