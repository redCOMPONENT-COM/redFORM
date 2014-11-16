<?php
/**
 * @package     Redform.Plugin
 * @subpackage  Redform.gaselkmd
 *
 * @copyright   Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * Handle ajax queries for gasel
 *
 * @package     Redform.Plugin
 * @subpackage  Redform.gaselkmd
 * @since       3.0
 */
class plgAjaxGaselkmd extends JPlugin
{
	/**
	 * Callback
	 *
	 * @return mixed
	 */
	public function onAjaxGaselkmd()
	{
		$function = JFactory::getApplication()->input->get('function');

		if (method_exists($this, $function))
		{
			return $this->{$function}();
		}
	}

	/**
	 * Get and return postcodes
	 *
	 * @return array
	 *
	 * @throws RuntimeException
	 */
	private function postcode()
	{
		$code = JFactory::getApplication()->input->get('q');

		$ch = curl_init();
		$url = "http://geo.oiorest.dk/postnumre.json?q=" . $code;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$json = curl_exec($ch);

		if (curl_exec($ch) === false)
		{
			throw new RuntimeException('Curl error: ' . curl_error($ch));
		}

		$data = json_decode($json);

		return $data;
	}

	/**
	 * Get and return postcodes
	 *
	 * @return array
	 *
	 * @throws RuntimeException
	 */
	private function street()
	{
		$code = JFactory::getApplication()->input->getInt('zip', 0);
		$street = JFactory::getApplication()->input->get('street');

		if (!$code || !$street)
		{
			return;
		}

		$ch = curl_init();
		$url = "http://geo.oiorest.dk/vejnavne.json?postnr=" . $code . "&vejnavn=" . urlencode($street) . "*";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$json = curl_exec($ch);

		if (curl_exec($ch) === false)
		{
			throw new RuntimeException('Curl error: ' . curl_error($ch));
		}

		$data = json_decode($json);

		return $data;
	}
}
