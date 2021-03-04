<?php
/**
 * @package     Redform.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

$data = $displayData;

$active = null;

if (isset($data['active']))
{
	$active = $data['active'];
}

$user = Factory::getUser();

$uri = Uri::getInstance();
$return = base64_encode('index.php' . $uri->toString(array('query')));

RHelperAsset::load('redformbackend.css', 'com_redform');
?>

<ul class="nav nav-tabs nav-stacked">
	<li>
		<a class="<?php echo ($active === 'dashboard' || !$active) ? 'active' : ''; ?>"
		   href="<?php echo Route::_('index.php?option=com_redform') ?>">
			<i class="icon-th"></i>
			<?php echo Text::_('COM_REDFORM_VIEW_TITLE_DASHBOARD') ?>
		</a>
	</li>
	<li>
		<a class="<?php echo $active === 'forms' ? 'active' : ''; ?>"
		   href="<?php echo Route::_('index.php?option=com_redform&view=forms') ?>">
			<i class="icon-list"></i>
			<?php echo Text::_('COM_REDFORM_FORM_LIST_TITLE') ?>
		</a>
	</li>
	<li>
		<a class="<?php echo $active === 'fields' ? 'active' : ''; ?>"
		   href="<?php echo Route::_('index.php?option=com_redform&view=fields') ?>">
			<i class="icon-check"></i>
			<?php echo Text::_('COM_REDFORM_FIELD_LIST_TITLE') ?>
		</a>
	</li>
	<li>
		<a class="<?php echo $active === 'sections' ? 'active' : ''; ?>"
		   href="<?php echo Route::_('index.php?option=com_redform&view=sections') ?>">
			<i class="icon-list"></i>
			<?php echo Text::_('COM_REDFORM_SECTION_LIST_TITLE') ?>
		</a>
	</li>
	<li>
		<a class="<?php echo $active === 'submitters' ? 'active' : ''; ?>"
		   href="<?php echo Route::_('index.php?option=com_redform&view=submitters') ?>">
			<i class="icon-user"></i>
			<?php echo Text::_('COM_REDFORM_SUBMITTER_LIST_TITLE') ?>
		</a>
	</li>
	<li>
		<a class="<?php echo $active === 'carts' ? 'active' : ''; ?>"
		   href="<?php echo Route::_('index.php?option=com_redform&view=carts') ?>">
			<i class="icon-shopping-cart"></i>
			<?php echo Text::_('COM_REDFORM_CART_LIST_TITLE') ?>
		</a>
	</li>
	<li>
		<a class="<?php echo $active === 'logs' ? 'active' : ''; ?>"
		   href="<?php echo Route::_('index.php?option=com_redform&view=logs') ?>">
			<i class="icon-comments"></i>
			<?php echo Text::_('COM_REDFORM_LOG_LIST_TITLE') ?>
		</a>
	</li>
	<?php if ($user->authorise('core.admin', 'com_redform')): ?>
	<li>
		<a class="<?php echo $active === 'config' ? 'active' : ''; ?>"
		   href="<?php echo Route::_('index.php?option=com_redcore&view=config&layout=edit&component=com_redform&return=' . $return); ?>">
			<i class="icon-cogs"></i>
			<?php echo Text::_('JToolbar_Options') ?>
		</a>
	</li>
	<?php endif; ?>
</ul>
