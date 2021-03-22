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

$class = array('infofield');

if ($data->getParam('class'))
{
	$class[] = $data->getParam('class');
}

$class = implode(' ', $class);
?>
<div class="<?php echo $class; ?>">
	<?php echo $data->getParam('content'); ?>
</div>
