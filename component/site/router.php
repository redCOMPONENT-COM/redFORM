<?php
/**
 * @package    Redform.Site
 *
 * @copyright  Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Build sef route
 *
 * @param   array  $query  query parts
 *
 * @return array
 */
function redformBuildRoute(&$query)
{
	$segments = array();

	if (isset($query['view']))
	{
		$segments[] = $query['view'];
		unset($query['view']);
	}
	else
	{
		// Only do sef if this is a view
		return $segments;
	}

	if (isset($query['submitKey']))
	{
		$segments[] = $query['submitKey'];
		unset($query['submitKey']);
	};

	if (isset($query['id']))
	{
		$segments[] = $query['id'];
		unset($query['id']);
	};

	return $segments;
}

/**
 * Parse sef route
 *
 * @param   array  $segments  segments
 *
 * @return array
 */
function redformParseRoute($segments)
{
	$vars = array();

	switch ($segments[0])
	{
		case 'notification':
			$vars['view'] = 'notification';
			$vars['submitKey'] = $segments[1];
			break;

		default:
			$vars['view'] = $segments[0];
	}

	return $vars;
}
