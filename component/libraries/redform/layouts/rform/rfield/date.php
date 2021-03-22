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
$attribs = array();

if (isset($properties['class']))
{
	$attribs['class'] = $properties['class'];
}

if (isset($properties['readonly']))
{
	$attribs['readonly'] = 'readonly';
}

if (isset($properties['dateformat']))
{
	$attribs['dateformat'] = $properties['dateformat'];
}

if (isset($properties['placeholder']))
{
	$attribs['placeholder'] = $properties['placeholder'];
}
?>
<?php echo JHTML::_('calendar', $data->getValue(), $properties['name'], $properties['id'],
	$properties['dateformat'],
	$attribs
);
