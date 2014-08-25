<?php
/**
 * @package    Redform.Site
 *
 * @copyright  Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

define('RDF_PATH_SITE',  JPATH_SITE . '/components/com_redform');
define('RDF_PATH_ADMIN', JPATH_SITE . '/administrator/components/com_redform');

JLoader::discover('RedformTable', RDF_PATH_ADMIN . '/tables');

$language = JFactory::getLanguage();
$language->load('com_redform');
