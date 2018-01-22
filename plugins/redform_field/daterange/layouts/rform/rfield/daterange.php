<?php
/**
 * @package     Redform.Site
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$data = $displayData;

$properties = $data->getInputProperties();
$properties['class'] = (empty($properties['class']) ? "" : $properties['class'] . " ") . "rfdaterange";

$doc = JFactory::getDocument();
$doc->addScript("//cdn.jsdelivr.net/momentjs/latest/moment.min.js");
$doc->addScript("//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js");
$doc->addStyleSheet("//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css");

RHelperAsset::load('script.js', 'plg_redform_field_daterange');

JText::script('PLG_REDFORM_FIELD_DATERANGE_JS_CLEAR');
?>
<input <?php echo $data->propertiesToString($properties); ?>/>