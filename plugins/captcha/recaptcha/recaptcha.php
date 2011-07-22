<?php
/**
* @version    $Id$ 
* @package    Xxxx
* @copyright  Copyright (C) 2008 Julien Vonthron. All rights reserved.
* @license    GNU/GPL, see LICENSE.php
* Xxxx is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
// Import library dependencies
jimport('joomla.event.plugin');

// load language file for frontend
JPlugin::loadLanguage( 'plg_redform_captcha_recaptcha', JPATH_ADMINISTRATOR );

class plgRedform_captchaRecaptcha extends JPlugin {
 
	public function plgRedform_captchaRecaptcha(&$subject, $config = array()) 
	{
		parent::__construct($subject, $config);
	}
	
	public function onGetCaptchaField(&$text)
	{
		require_once('recaptcha'.DS.'recaptchalib.php');
		$publickey = $this->params->get('public_key');
	  $text = plgRedformRecaptchaHelper::recaptcha_get_html($publickey, null, false, $this->params);
	  return true;
	}
	
	public function onCheckCaptcha(&$result)
	{
		require_once('recaptcha'.DS.'recaptchalib.php');
		$privatekey = $this->params->get('private_key');
	  $resp = plgRedformRecaptchaHelper::recaptcha_check_answer ($privatekey,
	                                $_SERVER["REMOTE_ADDR"],
	                                $_POST["recaptcha_challenge_field"],
	                                $_POST["recaptcha_response_field"]);
	  $result = $resp->is_valid;
	  return true;
	}
}
?>
