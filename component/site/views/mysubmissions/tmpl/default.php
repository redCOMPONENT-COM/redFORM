<?php
/**
 * @package    Redform.Site
 *
 * @copyright  Copyright (C) 2008 - 2017 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

// If the page class is defined, add to class as suffix.
// It will be a separate class if the user starts it with a space
?>
<div class="redform-submissions<?php echo $this->pageclass_sfx;?>">
	<?php if ($this->params->get('show_page_heading') != 0) : ?>
		<div class="page-header">
			<h1>
				<?php echo $this->escape($this->params->get('page_heading')); ?>
			</h1>
		</div>
	<?php endif; ?>

	<?php if ($this->showIntro): ?>
		<div class="introtext>">
			<?= $introtext ?>
		</div>
	<?php endif; ?>

	<?php if (!empty($this->items)) : ?>
		<div class="items">
			<?php echo $this->loadTemplate('items'); ?>
		</div>
	<?php endif; ?>

	<?php if ($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2 && $this->pagination->pagesTotal > 1)) : ?>
		<div class="pagination">

			<?php if ($this->params->def('show_pagination_results', 1)) : ?>
				<p class="counter pull-right">
					<?php echo $this->pagination->getPagesCounter(); ?>
				</p>
			<?php  endif; ?>
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	<?php endif; ?>

</div>
