<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Class RdfHelperRoute
 *
 * @package     Redform.Libraries
 * @subpackage  Helper
 * @since       2.5
 */
class RdfHelperRoute
{
	protected static $lookup;

	/**
	 * Route to My submissions
	 *
	 * @return string
	 */
	public static function getMysubmissionsRoute()
	{
		$parts = array(
			"option" => "com_redform",
			"view"   => 'mysubmissions',
		);

		if ($itemId = self::findItem($parts))
		{
			$parts['Itemid'] = $itemId;
		}

		return self::buildUrl($parts);
	}

	/**
	 * Route to payment
	 *
	 * @param   string  $submit_key  submit keys
	 *
	 * @return string
	 */
	public static function getPaymentRoute($submit_key)
	{
		$parts = array( "option" => "com_redform",
			"task"   => 'payment.select',
			"key"   => $submit_key,
		);

		if ($itemId = self::findItem($parts))
		{
			$parts['Itemid'] = $itemId;
		}

		return self::buildUrl($parts);
	}

	/**
	 * Route to payment
	 *
	 * @param   int  $cartId  cart id
	 *
	 * @return string
	 */
	public static function getPaymentCartRoute($cartId)
	{
		$parts = array( "option" => "com_redform",
			"task"   => 'payment.select',
			"cart_id"   => $cartId,
		);

		if ($itemId = self::findItem($parts))
		{
			$parts['Itemid'] = $itemId;
		}

		return self::buildUrl($parts);
	}

	/**
	 * Route to payment process
	 *
	 * @param   string  $cartReference  submit keys
	 * @param   string  $gateway        gateway name
	 *
	 * @return string
	 */
	public static function getPaymentProcessRoute($cartReference, $gateway)
	{
		$parts = array( "option" => "com_redform",
			"task"   => 'payment.process',
			"gw"   => $gateway,
			"reference"   => $cartReference,
		);

		if ($itemId = self::findItem($parts))
		{
			$parts['Itemid'] = $itemId;
		}

		return self::buildUrl($parts);
	}

	/**
	 * Get submission route
	 *
	 * @param   int  $submissionId  submission id
	 *
	 * @return string
	 */
	public static function getSubmissionRoute($submissionId)
	{
		$parts = array(
			"option" => "com_redform",
			"view"   => 'submission',
			'id' => $submissionId
		);

		if ($itemId = self::findItem($parts))
		{
			$parts['Itemid'] = $itemId;
		}

		return self::buildUrl($parts);
	}

	/**
	 * Get submission route
	 *
	 * @param   int  $submissionId  submission id
	 *
	 * @return string
	 */
	public static function getSubmissionEditRoute($submissionId)
	{
		$parts = array(
			"option" => "com_redform",
			"task"   => 'submission.edit',
			'id' => $submissionId
		);

		if ($itemId = self::findItem($parts))
		{
			$parts['Itemid'] = $itemId;
		}

		return self::buildUrl($parts);
	}

	/**
	 * Returns the route from parts
	 *
	 * @param   array  $parts  segments of the route
	 *
	 * @return string
	 */
	protected static function buildUrl($parts)
	{
		return 'index.php?' . JURI::buildQuery($parts);
	}

	/**
	 * Find items
	 *
	 * @param   array  $parts  array of require
	 *
	 * @return  string
	 */
	protected static function findItem($parts)
	{
		$app = JFactory::getApplication();
		$menus = $app->getMenu('site');

		// Prepare the reverse lookup array of menu items.
		if (self::$lookup === null)
		{
			self::$lookup = array();

			$component = JComponentHelper::getComponent('com_redform');

			$items = $menus->getItems('component_id', $component->id);

			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];

					if (!isset(self::$lookup[$view]))
					{
						self::$lookup[$view] = array();
					}

					if ($view == 'mysubmissions')
					{
						self::$lookup[$view] = $item->id;
					}
				}
			}
		}

		if (!empty($parts['view']))
		{
			// For now, all we have is mysubmissions or submissions.
			// It's not possible to give submission a menu item, so let's associate them with mysubmissions view menu item if exists
			if ($parts['view'] == 'mysubmissions' || $parts['view'] == 'submission')
			{
				return !empty(self::$lookup['mysubmissions']) ? self::$lookup['mysubmissions'] : null;
			}
		}
		else
		{
			$active = $menus->getActive();

			if ($active)
			{
				return $active->id;
			}
		}

		return null;
	}

	/**
	 * Search a menu Item id.
	 *
	 * @param   array  $parts  Query parts to search for.
	 *
	 * @return  mixed  Integer if found | null otherwise
	 */
	public static function searchItemId($parts)
	{
		return self::findItem($parts);
	}
}
