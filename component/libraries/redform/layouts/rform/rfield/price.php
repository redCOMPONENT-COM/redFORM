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

if ($data->options)
{
	echo $this->sublayout('fixed', $displayData);
}
else
{
	echo $this->sublayout('input', $displayData);
}
