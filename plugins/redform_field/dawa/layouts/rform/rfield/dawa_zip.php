<?php
/**
 * @package     Redform.Site
 * @subpackage  Layouts
 *
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$data = $displayData;

$properties = $data->getInputProperties();

RHelperAsset::load('jquery.autocomplete.js', 'plg_redform_field_dawa');
RHelperAsset::load('dawa.js', 'plg_redform_field_dawa');
?>
<input <?php echo $data->propertiesToString($properties); ?>/>
