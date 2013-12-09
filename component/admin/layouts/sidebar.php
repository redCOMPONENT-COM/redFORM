<?php
/**
 * @package     Redshopb.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$data = $displayData;

$active = null;

$useracl = JFactory::getUser();

if (isset($data['active']))
{
	$active = $data['active'];
}

$dashboardClass = ($active === 'dashboard') ? 'active' : '';
$usersClass = ($active === 'users') ? 'active' : '';
$companiesClass = ($active === 'companies') ? 'active' : '';
$departmentsClass = ($active === 'departments') ? 'active' : '';
$wardrobesClass = ($active === 'wardrobes') ? 'active' : '';
$productsClass = ($active === 'products') ? 'active' : '';
$categoriesClass = ($active === 'categories') ? 'active' : '';
$currenciesClass = ($active === 'currencies') ? 'active' : '';
$ordersClass = ($active === 'orders') ? 'active' : '';
$layoutsClass = ($active === 'layouts') ? 'active' : '';
$form = JFactory::getApplication()->input->get('jform', array(), 'array');
$userId = null;
?>

<ul class="nav nav-tabs nav-stacked">
	<li>
		<a class="<?php echo $usersClass ?>"
		   href="<?php echo JRoute::_('index.php?option=com_redform&view=forms') ?>">
			<i class="icon-user"></i>
			<?php echo JText::_('COM_REDFORM_FORM_LIST_TITLE') ?>
		</a>
	</li>
</ul>
