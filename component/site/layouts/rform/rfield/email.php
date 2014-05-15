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

$properties = $data->getInputProperties();
$lists = $data->getParam('listname');
?>
<div class="emailfields">
	<div class="emailfield">
		<input <?php echo $data->propertiesToString($properties); ?> />
	</div>

	<?php if (count($lists) && $lists[0]): ?>
		<?php echo $this->sublayout('newsletters', $displayData); ?>
	<?php endif; ?>
</div>
