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
