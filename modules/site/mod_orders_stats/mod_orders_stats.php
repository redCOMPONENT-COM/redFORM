<?php
/**
 * @package     Redform.Frontend
 * @subpackage  mod_orders_stats
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Load redCORE library
$redcoreLoader = JPATH_LIBRARIES . '/redcore/bootstrap.php';

if (!file_exists($redcoreLoader) || !JPluginHelper::isEnabled('system', 'redcore'))
{
	throw new Exception(JText::_('COM_REDITEM_REDCORE_INIT_FAILED'), 404);
}

include_once $redcoreLoader;

RBootstrap::bootstrap();

require_once __DIR__ . '/helper.php';

// Prepare for cache
$cacheparams = new stdClass;
$cacheparams->cachemode = 'static';
$cacheparams->class = 'ModOrdersStatsHelper';
$cacheparams->method = 'getList';
$cacheparams->methodparams = $params;

$items = JModuleHelper::moduleCache($module, $params, $cacheparams);

require JModuleHelper::getLayoutPath('mod_orders_stats', $params->get('layout', 'default'));
