<?php
/**
* @version    $Id$ 
* @package    Xxxx
* @copyright  Copyright (C) 2008 Julien Vonthron. All rights reserved.
* @license    GNU/GPL, see LICENSE.php
* Xxxx is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// get helper
// require_once (dirname(__FILE__).DS.'helper.php');

require_once(JPATH_SITE.DS.'components'.DS.'com_redform'.DS.'redform.core.php');

// $list = modReformHelper::getList($params);

$document = & JFactory::getDocument();
//add css file
$document->addStyleSheet(JURI::base().'modules/mod_redform_form/mod_redform_form.css');

require(JModuleHelper::getLayoutPath('mod_redform_form'));