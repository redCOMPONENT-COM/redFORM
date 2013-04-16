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

/**
 * Helper class
 *
 * @package     redFORM
 * @subpackage  component
 */
class redFORMHelperAnalytics
{
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
		  })(window,document,'script','//www.google-analytics.com/analytics.js','gared');

		  gared('create', '{$params->get('ga_code')}');
		  gared('require', 'ecommerce', 'ecommerce.js');
		  gared('send', 'pageview');
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
	 * @return void
	 */
	public static function addTrans($trans)
	{
		$params = JComponentHelper::getParams('com_redform');

		$js_ua = <<<JS
			gared('ecommerce:addTransaction', {
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
		JFactory::getDocument()->addScriptDeclaration($params->get('ga_mode', 0) ? $js_classic : $js_ua);
	}

	public static function addItem($item)
	{
		$params = JComponentHelper::getParams('com_redform');

		$js_ua = <<<JS
			gared('ecommerce:addItem', {
			'id' : '{$item->id}',  // Transaction ID. Required.
			'name' : '{$item->name}',      // Product name. Required.
			'price' : '{$item->price}'    // Unit price.
			});
JS;

		$js_classic = <<<JS
			_gaq.push(['_addItem',
				'{$item->id}',  // Transaction ID. Required.
				'{$item->name}',        // SKU/code - required			.
				'{$item->name}',        // Product name.
				'{$item->price}'       // Unit price.
				]);
JS;
		JFactory::getDocument()->addScriptDeclaration($params->get('ga_mode', 0) ? $js_classic : $js_ua);
	}

	/**
	 * tracks an event
	 *
	 * @param   object  $event  event data
	 *
	 * @return void
	 */
	public static function trackEvent($event)
	{
		$params = JComponentHelper::getParams('com_redform');

		$value = $event->value ? $event->value : 1;

		$js_ua = <<<JS
			gared('send', 'event',
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
		JFactory::getDocument()->addScriptDeclaration($params->get('ga_mode', 0) ? $js_classic : $js_ua);
	}
}
