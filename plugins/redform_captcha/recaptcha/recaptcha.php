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
		JFactory::getDocument()->addScript('https://www.google.com/recaptcha/api.js', null, true, true);

		$publickey = $this->params->get('public_key');
		$text = '<div class="g-recaptcha" data-sitekey="' . $publickey . '"></div>';

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
		$privatekey = $this->params->get('private_key');
		$gRecaptchaResponse = JFactory::getApplication()->input->get('g-recaptcha-response');

		$recaptcha = new \ReCaptcha\ReCaptcha($privatekey);
		$resp = $recaptcha->verify($gRecaptchaResponse, $_SERVER["REMOTE_ADDR"]);

		$result = $resp->isSuccess();

		return true;
	}
}
