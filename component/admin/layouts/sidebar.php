<?php
/**
 * @package     Redform.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$data = $displayData;

$active = null;

if (isset($data['active']))
{
	$active = $data['active'];
}

$user = JFactory::getUser();

$uri = JUri::getInstance();
$return = base64_encode('index.php' . $uri->toString(array('query')));

RHelperAsset::load('redformbackend.css', 'com_redform');
?>

<ul class="nav nav-tabs nav-stacked">
	<li>
		<a class="<?php echo ($active === 'dashboard' || !$active) ? 'active' : ''; ?>"
		   href="<?php echo JRoute::_('index.php?option=com_redform') ?>">
			<i class="icon-th"></i>
			<?php echo JText::_('COM_REDFORM_VIEW_TITLE_DASHBOARD') ?>
		</a>
	</li>
	<li>
		<a class="<?php echo $active === 'forms' ? 'active' : ''; ?>"
		   href="<?php echo JRoute::_('index.php?option=com_redform&view=forms') ?>">
			<i class="icon-list"></i>
			<?php echo JText::_('COM_REDFORM_FORM_LIST_TITLE') ?>
		</a>
	</li>
	<li>
		<a class="<?php echo $active === 'fields' ? 'active' : ''; ?>"
		   href="<?php echo JRoute::_('index.php?option=com_redform&view=fields') ?>">
			<i class="icon-check"></i>
			<?php echo JText::_('COM_REDFORM_FIELD_LIST_TITLE') ?>
		</a>
	</li>
	<li>
		<a class="<?php echo $active === 'sections' ? 'active' : ''; ?>"
		   href="<?php echo JRoute::_('index.php?option=com_redform&view=sections') ?>">
			<i class="icon-list"></i>
			<?php echo JText::_('COM_REDFORM_SECTION_LIST_TITLE') ?>
		</a>
	</li>
	<li>
		<a class="<?php echo $active === 'submitters' ? 'active' : ''; ?>"
		   href="<?php echo JRoute::_('index.php?option=com_redform&view=submitters') ?>">
			<i class="icon-user"></i>
			<?php echo JText::_('COM_REDFORM_SUBMITTER_LIST_TITLE') ?>
		</a>
	</li>
	<li>
		<a class="<?php echo $active === 'carts' ? 'active' : ''; ?>"
		   href="<?php echo JRoute::_('index.php?option=com_redform&view=carts') ?>">
			<i class="icon-shopping-cart"></i>
			<?php echo JText::_('COM_REDFORM_CART_LIST_TITLE') ?>
		</a>
	</li>
	<li>
		<a class="<?php echo $active === 'logs' ? 'active' : ''; ?>"
		   href="<?php echo JRoute::_('index.php?option=com_redform&view=logs') ?>">
			<i class="icon-comments"></i>
			<?php echo JText::_('COM_REDFORM_LOG_LIST_TITLE') ?>
		</a>
	</li>
	<?php if ($user->authorise('core.admin', 'com_redform')): ?>
	<li>
		<a class="<?php echo $active === 'config' ? 'active' : ''; ?>"
		   href="<?php echo JRoute::_('index.php?option=com_redcore&view=config&layout=edit&component=com_redform&return=' . $return); ?>">
			<i class="icon-cogs"></i>
			<?php echo JText::_('JToolbar_Options') ?>
		</a>
	</li>
	<?php endif; ?>
</ul>
