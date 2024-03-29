<?php
/**
 * @package     Redform.Site
 * @subpackage  Layouts
 *
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
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

	<?php if (is_array($lists) && count($lists) && $lists[0]): ?>
		<?php echo $this->sublayout('newsletters', $displayData); ?>
	<?php endif; ?>
</div>
