<?php
/**
 * @package     Redform.plugins
 * @subpackage  captcha
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die( 'Restricted access');

// Import library dependencies
jimport('joomla.plugin.plugin');

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
		require_once 'recaptcha/recaptchalib.php';
		$publickey = $this->params->get('public_key');
		$text = plgRedformRecaptchaHelper::recaptcha_get_html($publickey, null, false, $this->params);

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
		require_once 'recaptcha/recaptchalib.php';
		$privatekey = $this->params->get('private_key');
		$resp = plgRedformRecaptchaHelper::recaptcha_check_answer(
			$privatekey,
			$_SERVER["REMOTE_ADDR"],
			$_POST["recaptcha_challenge_field"],
			$_POST["recaptcha_response_field"]
		);
		$result = $resp->is_valid;

		return true;
	}
}
