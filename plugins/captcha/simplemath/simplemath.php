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
jimport('joomla.plugin.plugin');

class plgRedform_captchaSimplemath extends JPlugin {
 
	public function __construct(&$subject, $config = array()) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}
	
	/**
	 * the data to write on the form
	 * @param string $text
	 */
	public function onGetCaptchaField(&$text)
	{
		$session = JFactory::getSession();

		$a = intval(mt_rand(1,100) / 10);
		$b = intval(mt_rand(1,100) / 10);
		
		$session->set('session.simplemath', $a + $b);
				
		$text =  JText::_('PLG_REDFORM_CAPTCHA_CAPTCHA_LABEL').' '
		       . JText::sprintf('PLG_REDFORM_CAPTCHA_CAPTCHA_WHAT_IS_D_PLUS_D', $a, $b)
		       . '<br/>'		
		       . '<input type="text" name="sm_answer" />';
	  return true;
	}
	
	/**
	 * the function that does the result check
	 * @param boolean $result
	 */
	public function onCheckCaptcha(&$result)
	{
		$session = JFactory::getSession();
		$res = $session->get('session.simplemath');
		
		$posted = JRequest::getInt('sm_answer', -1, 'post');
		
	  $result = $res == $posted;
	  return true;
	}
}
