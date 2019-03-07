<?php
/**
 * @package     Redform.plugins
 * @subpackage  captcha
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

// Import library dependencies
jimport('joomla.plugin.plugin');

include_once 'vendor/autoload.php';

/**
 * Class plgRedform_captchaRecaptcha
 *
 * @package  Redform.plugins
 * @since    2.5
 */
class PlgRedform_captchaRecaptcha extends JPlugin
{
	private $version;

	private $publicKey;

	private $privateKey;

	private $apiScript = 'https://www.google.com/recaptcha/api.js';

	private $expectedAction;

	private $thresholdScore;

	private $responseElementId = 'g-recaptcha-response';

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();

		$this->version = $this->params->get('version');

		if ($this->version == 2)
		{
			$this->publicKey  = $this->params->get('public_key_v2');
			$this->privateKey = $this->params->get('private_key_v2');
		}

		if ($this->version == 3)
		{
			$this->publicKey      = $this->params->get('public_key_v3');
			$this->privateKey     = $this->params->get('private_key_v3');
			$this->thresholdScore = $this->params->get('min_accepted_score_v3');
			$this->expectedAction = $this->params->get('expected_action_v3');
		}
	}

	/**
	 * onGetCaptchaField trigger
	 *
	 * @param   string  &$text  text to modify
	 *
	 * @return bool
	 */
	public function onGetCaptchaField(&$text)
	{
		$document                = JFactory::getDocument();
		$differentiator          = rand();
		$responseElementIdUnique = $this->responseElementId . $differentiator;

		if ($this->version == 2)
		{
			$document->addScript($this->apiScript);
		}

		if ($this->version == 3)
		{
			$document->addScript($this->apiScript . '?render=' . $this->publicKey);

            $document->addScriptDeclaration('
				grecaptcha.ready(function() 
				{
					grecaptcha.execute("' . $this->publicKey . '", {action: "' . $this->expectedAction . '"}).then(function(token)
					{
						document.getElementById("' . $responseElementIdUnique . '").value = token;
					});
				});
			');
		}

		$attributes = array();

		$attributes['data-sitekey'] = $this->publicKey;

		if ($this->params->get('theme'))
		{
			$attributes['data-theme'] = $this->params->get('theme');
		}

		if ($this->params->get('size'))
		{
			$attributes['data-size'] = $this->params->get('size');
		}

		if ($this->version == 2)
		{
			$text = '<div class="g-recaptcha"' . JArrayHelper::toString($attributes, '=', ' ') . '></div>';
		}

		if ($this->version == 3)
		{
			$text = '<input type="hidden" id="' . $responseElementIdUnique . '" name="' . $responseElementIdUnique . '">';

			$text .= '<input type="hidden" name="differentiator" value="' . $differentiator . '">';
		}

		return true;
	}

	/**
	 * onCheckCaptcha trigger
	 *
	 * @param   bool  &$result  result
	 *
	 * @return bool
	 */
	public function onCheckCaptcha(&$result)
	{
		require_once 'vendor/autoload.php';

		$app                = JFactory::getApplication();
		$differentiator     = $app->input->get('differentiator');
		$gRecaptchaResponse = $app->input->get($this->responseElementId . $differentiator);

		$recaptcha = new \ReCaptcha\ReCaptcha($this->privateKey);

		if ($this->version == 3)
		{
			$recaptcha
				->setExpectedHostname($_SERVER['SERVER_NAME'])
				->setExpectedAction($this->expectedAction)
				->setScoreThreshold($this->thresholdScore);
		}

		$resp = $recaptcha->verify($gRecaptchaResponse, $_SERVER["REMOTE_ADDR"]);

//		Example of $resp
//
//		object(ReCaptcha\Response)#605 (7)
//		{
//			["success":"ReCaptcha\Response":private]=> bool(true)
//			["errorCodes":"ReCaptcha\Response":private]=> array(0) { }
//			["hostname":"ReCaptcha\Response":private]=> string(33) "www.staging.pconradsen.redhost.dk"
//			["challengeTs":"ReCaptcha\Response":private]=> string(20) "2018-10-24T03:20:33Z"
//			["apkPackageName":"ReCaptcha\Response":private]=> NULL
//			["score":"ReCaptcha\Response":private]=> float(0.9)
//			["action":"ReCaptcha\Response":private]=> string(8) "homepage"
//		}

		$result = $resp->isSuccess();

		return $result;
	}
}
