<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license GNU/GPL, see LICENSE.php
 * redFORM can be downloaded from www.redcomponent.com
 * redFORM is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redFORM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redFORM; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Frontend file
 */

/**
 */ 
// no direct access
defined('_JEXEC') or die('Restricted access');

define('RDF_PATH_SITE',  JPATH_SITE . DS . 'components' .DS . 'com_redform'); 
define('RDF_PATH_ADMIN', JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_redform');

JLoader::discover('RedformTable', RDF_PATH_ADMIN.DS.'tables');

$language = JFactory::getLanguage();
$language->load('com_redform');