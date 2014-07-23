<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Analytics
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * google Measurement protocol api
 *
 * @package     Redform.Libraries
 * @subpackage  Analytics
 * @since       2.5
 */
class RedformAnalyticsMeasurementprotocolClient implements RedformAnalyticsMeasurementprotocolClientinterface
{
	/**
	 * @var int
	 */
	private $protocolVersion = 1;

	/**
	 * @var string
	 */
	private $trackingId;

	/**
	 * anonymous client id
	 * @var string uuid
	 */
	private $clientId;

	/**
	 * constructor
	 *
	 * @param   array  $config  clientId, trackingId
	 */
	public function __construct($config = array())
	{
		$configRegistry = new JRegistry($config);

		if ($val = $configRegistry->get('clientId'))
		{
			$this->setClientId($val);
		}
		else
		{
			$this->setClientId($this->generateClientId());
		}

		if ($val = $configRegistry->get('trackingId'))
		{
			$this->trackingId = $val;
		}
		else
		{
			$this->getTrackingIdFromConfig();
		}
	}

	/**
	 * setter
	 *
	 * @param   string  $clientId  client uuid
	 *
	 * @return $this
	 */
	public function setClientId($clientId)
	{
		$this->clientId = $clientId;

		return $this;
	}

	/**
	 * Send hit
	 *
	 * @param   array  $data  data to send
	 *
	 * @return bool
	 */
	public function hit($data)
	{
		$base = array(
			'v' => $this->protocolVersion,
			'tid' => $this->trackingId,
			'cid' => $this->clientId
		);

		$data = array_merge($base, $data);

		return $this->send($data);
	}

	/**
	 * Send the data using curl
	 *
	 * @param   array  $data  data to send
	 *
	 * @return bool
	 */
	private function send($data)
	{
		$url = 'http://www.google-analytics.com/collect';
		$content = http_build_query($data);
		$content = utf8_encode($content);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded'));
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
		curl_exec($ch);

		if (!$ch_result = curl_exec($ch))
		{
			RedformHelperLog::simpleLog('Gua mp error: ' . curl_error($ch));

			return false;
		}

		curl_close($ch);

		return true;
	}

	/**
	 * Get tracking id from config
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	private function getTrackingIdFromConfig()
	{
		$params = JComponentHelper::getParams('com_redform');

		if (!$params->get('ga_code'))
		{
			throw new InvalidArgumentException('Missing ga code');
		}

		$this->trackingId = $params->get('ga_code');
	}

	/**
	 * Generate UUID v4 function - needed to generate a CID when one isn't available
	 *
	 * @author Andrew Moore http://www.php.net/manual/en/function.uniqid.php#94959
	 * @return string
	 */
	private function generateClientId()
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),

			// 16 bits for "time_mid"
			mt_rand(0, 0xffff),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,

			// 48 bits for "node"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}
}
