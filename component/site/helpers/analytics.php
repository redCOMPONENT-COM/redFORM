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
 */

defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/components/com_redform/models/payment.php';

/**
 * Helper class
 *
 * @package     redFORM
 * @subpackage  component
 */
class redFORMHelperAnalytics
{
	/**
	 * return true if GA is enabled
	 *
	 * @return boolean
	 */
	public static function isEnabled()
	{
		$params = JComponentHelper::getParams('com_redform');

		return $params->get('enable_ga', 0) ? true : false;
	}

	/**
	 * load google analytics
	 *
	 * @return boolean true if analytics is enabled
	 */
	public static function load()
	{
		$params = JComponentHelper::getParams('com_redform');

		if (!$params->get('enable_ga', 0) || !$params->get('ga_code'))
		{
			return false;
		}

		$js_ua = <<<JS
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		  ga('create', '{$params->get('ga_code')}');
		  ga('send', 'pageview');
JS;
		$js_classic = <<<JS
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', '{$params->get('ga_code')}']);

		  (function() {
		    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
JS;

		JFactory::getDocument()->addScriptDeclaration($params->get('ga_mode', 0) ? $js_classic : $js_ua);

		return true;
	}

	/**
	 * adds transaction for google ecommerce tracking
	 *
	 * @param   object  $trans  transaction details
	 *
	 * @return string js code
	 */
	public static function addTrans($trans)
	{
		$params = JComponentHelper::getParams('com_redform');

		$js_ua = <<<JS
		  	ga('require', 'ecommerce', 'ecommerce.js');
			ga('ecommerce:addTransaction', {
			'id' : '{$trans->id}',           // transaction ID - required
			'affiliation' : '{$trans->affiliation}',  // affiliation or store name
			'revenue' : '{$trans->revenue}'          // total - required
			});
JS;

		$js_classic = <<<JS
		_gaq.push(['_addTrans',
			'{$trans->id}',           // transaction ID - required
			'{$trans->affiliation}',  // affiliation or store name
			'{$trans->revenue}'          // total - required
			]);
JS;
		$js = $params->get('ga_mode', 0) ? $js_classic : $js_ua;
		JFactory::getDocument()->addScriptDeclaration($js);

		return $js;
	}

	/**
	 * add item for transaction
	 *
	 * @param   object  $item      item to be added (id, sku, productname, category, price)
	 * @param   int     $quantity  quantity
	 *
	 * @return string js code
	 */
	public static function addItem($item, $quantity = 1)
	{
		$params = JComponentHelper::getParams('com_redform');

		$js_ua = <<<JS
			ga('ecommerce:addItem', {
			'id' : '{$item->transaction_id}',  // Transaction ID. Required.
			'name' : '{$item->productname}',      // Product name. Required.
			'sku' : '{$item->sku}',        // SKU/code - required			.
			'category' : '{$item->category}',      // Product name. Required.
			'price' : '{$item->price}',    // Unit price.
			'quantity' : '{$quantity}'    // Unit quantity.
			});
JS;

		$js_classic = <<<JS
			_gaq.push(['_addItem',
				'{$item->id}',  // TTransaction ID. Required.
				'{$item->sku}',        // SKU/code - required			.
				'{$item->productname}',        // Product name.		.
				'{$item->category}',        // Category.
				'{$item->price}',       // Unit price - required
				'{$quantity}'    // Unit quantity- required
				]);
JS;
		$js = $params->get('ga_mode', 0) ? $js_classic : $js_ua;
		JFactory::getDocument()->addScriptDeclaration($js);

		return $js;
	}

	/**
	 * add tracktrans code
	 *
	 * @return string js code
	 */
	public static function trackTrans()
	{
		$params = JComponentHelper::getParams('com_redform');

		$js_ua = <<<JS
			ga('ecommerce:send');
JS;

		$js_classic = <<<JS
			_gaq.push(['_trackTrans']);
JS;
		$js = $params->get('ga_mode', 0) ? $js_classic : $js_ua;
		JFactory::getDocument()->addScriptDeclaration($js);

		return $js;
	}

	/**
	 * Adds a pageview
	 *
	 * @param   sting  $page  optional page name
	 *
	 * @return string js code
	 */
	public static function pageView($page = null)
	{
		$params = JComponentHelper::getParams('com_redform');

		if ($page)
		{
			$js_ua = "ga('send', 'pageview', '{$page}');";
			$js_classic = "_gaq.push(['_trackPageview',	'{$page}']);";
		}
		else
		{
			$js_ua = "ga('send', 'pageview');";
			$js_classic = "_gaq.push(['_trackPageview']);";
		}

		$js = $params->get('ga_mode', 0) ? $js_classic : $js_ua;
		JFactory::getDocument()->addScriptDeclaration($js);

		return $js;
	}

	/**
	 * tracks an event
	 *
	 * @param   object  $event  event data
	 *
	 * @return string js code
	 */
	public static function trackEvent($event)
	{
		$params = JComponentHelper::getParams('com_redform');

		$value = $event->value ? $event->value : 1;

		$js_ua = <<<JS
			ga('send', 'event',
			'{$event->category}',
			'{$event->action}',
			'{$event->label}',
			$value
			);
JS;

		$js_classic = <<<JS
			_gaq.push(['_trackEvent',
				'{$event->category}',
				'{$event->action}',
				'{$event->label}',
				$value
				]);
JS;
		$js = $params->get('ga_mode', 0) ? $js_classic : $js_ua;
		JFactory::getDocument()->addScriptDeclaration($js);

		return $js;
	}

	/**
	 * full transaction tracking. adds javsacript code to document head
	 *
	 * @param   String  $submit_key  submit key to add transaction for
	 * @param   Array   $options     optional parameters for tracking
	 *
	 * @return string js code
	 */
	public static function recordTrans($submit_key, array $options = array())
	{
		$model = JModel::getInstance('payment', 'RedformModel');
		$model->setSubmitKey($submit_key);
		$submitters = $model->getSubmitters();
		$payment   = $model->getPaymentDetails($submit_key);

		// Add transaction
		$trans = new stdclass;
		$trans->id = $submit_key;
		$trans->affiliation = isset($options['affiliate']) ? $options['affiliate'] : $payment->form;
		$trans->revenue = $model->getPrice();

		$js = self::addTrans($trans);

		$productname = isset($options['productname']) ? $options['productname'] : null;
		$sku         = isset($options['sku']) ? $options['sku'] : null;
		$category    = isset($options['category']) ? $options['category'] : null;

		// Add submitters as items
		foreach ($submitters as $s)
		{
			$item = new stdclass;
			$item->id = $submit_key;
			$item->productname = $productname ? $productname : 'submitter' . $s->id;
			$item->sku  = $sku ? $sku : 'submitter' . $s->id;
			$item->category  = $category ? $category : '';
			$item->price = $s->price;
		}
		$js .= self::addItem($item);

		// push transaction to server
		$js .= self::trackTrans();

		return $js;
	}
}
