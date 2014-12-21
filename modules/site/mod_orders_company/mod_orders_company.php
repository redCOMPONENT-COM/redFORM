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

RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');
RLoader::registerPrefix('Redform', JPATH_ADMINISTRATOR . '/components/com_redform');
RLoader::registerPrefix('Modorderscompany', __DIR__);

// Prepare for cache
$cacheparams = new stdClass;
$cacheparams->cachemode = 'static';
$cacheparams->class = 'ModorderscompanyLibHelper';
$cacheparams->method = 'getData';
$cacheparams->methodparams = $params;

$data = JModuleHelper::moduleCache($module, $params, $cacheparams);

require JModuleHelper::getLayoutPath('mod_orders_company', $params->get('layout', 'default'));
