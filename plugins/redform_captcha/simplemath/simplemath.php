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
 * Class plgRedform_captchaSimplemath
 *
 * @package  Redform.plugins
 * @since    2.5
 */
class PlgRedform_captchaSimplemath extends JPlugin
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
		$session = JFactory::getSession();

		$a = intval(mt_rand(1, 100) / 10);
		$b = intval(mt_rand(1, 100) / 10);

		$hash = uniqid();

		$session->set('session.simplemath' . $hash, $a + $b);

		$text = JText::_('PLG_REDFORM_CAPTCHA_CAPTCHA_LABEL') . ' '
			. JText::sprintf('PLG_REDFORM_CAPTCHA_CAPTCHA_WHAT_IS_D_PLUS_D', $a, $b)
			. '<br/>'
			. '<input type="text" name="sm_answer" />'
			. '<input type="hidden" name="sm_hash" value="' . $hash . '" />';

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
		$session = JFactory::getSession();
		$input = JFactory::getApplication()->input;

		$hash = $input->getCmd('sm_hash', null);
		$res = $session->get('session.simplemath' . $hash);
		$session->set('session.simplemath' . $hash, null);

		$posted = $input->getInt('sm_answer', -1);

		$result = $res == $posted;

		return true;
	}
}
