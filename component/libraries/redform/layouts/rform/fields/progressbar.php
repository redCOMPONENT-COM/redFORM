<?php
/**
 * @package     Redform.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$sections = $displayData;

$n = count($sections);

if ($n < 2)
{
	// We only need this is there are more than 1 section
	return;
}

RHelperAsset::load('progressbar/progressbar.css', 'com_redform');
RHelperAsset::load('progressbar/progressbar.js', 'com_redform');

$i = 1;
?>
<div class="form-progress">
	<?php foreach ($sections as $section): ?>
		<?php $section = RdfEntitySection::load($section->id); ?>
		<div class="circle<?php echo 1 == $i ? ' active' : ''; ?>">
			<span class="label"><?php echo $i; ?></span>
			<span class="title"><?php echo $section->name; ?></span>
		</div>
		<?php if ($i++ < $n): ?>
			<span class="bar"></span>
		<?php endif; ?>
	<?php endforeach; ?>
</div>
