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

/**
 * Class plgRedform_captchaHiddencaptcha
 *
 * @package  Redform.plugins
 * @since    2.5
 */
class plgRedform_captchaHiddencaptcha extends JPlugin
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
		$text = '<input type="text" id="url" name="url" value="" />'
			. '<script type="text/javascript">'
			. 'document.getElementById("url").style.display = "none";'
			. 'for (var i=0;i<document.getElementsByTagName("label").length;i++) {'
			. 'if (document.getElementsByTagName("label")[i].firstChild.data == "Captcha Check") {'
			. 'document.getElementsByTagName("label")[i].firstChild.data = ""'
			. '}'
			. '}'
			. '</script>';

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
		$posted = JFactory::getApplication()->input->getString('url', -1, 'post');

		$result = $posted == "";

		return true;
	}
}
