<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Class RdfMailer
 *
 * @package     Redform.Libraries
 * @subpackage  Helper
 * @since       3.0
 */
class RdfHelperMailer extends JMail
{
	/**
	 * Wrap email content in proper html
	 *
	 * @param   string  $body     content of body tag
	 * @param   string  $subject  subject of the email
	 *
	 * @return string
	 */
	public static function wrapMailHtmlBody($body, $subject)
	{
		return RdfLayoutHelper::render('email.bodywrapper',
			array('body' => $body, 'subject' => $subject),
			'',
			array('component' => 'com_redform')
		);
	}

	/**
	 * Override for 3.5.1 new behavior
	 *
	 * @param   mixed   $replyto  reply to
	 * @param   string  $name     name
	 *
	 * @return JMail
	 */
	public function addReplyTo($replyto, $name = '')
	{
		if (is_array($replyto) && version_compare(JVERSION, '3.5', 'ge'))
		{
			return parent::addReplyTo($replyto[0], $replyto[1]);
		}

		return parent::addReplyTo($replyto, $name);
	}

	/**
	 * Returns the global email object, only creating it
	 * if it doesn't already exist.
	 *
	 * NOTE: If you need an instance to use that does not have the global configuration
	 * values, use an id string that is not 'Joomla'.
	 *
	 * @param   string   $id          The id string for the JMail instance [optional]
	 * @param   boolean  $exceptions  Flag if Exceptions should be thrown [optional]
	 *
	 * @return  JMail  The global JMail object
	 *
	 * @since   11.1
	 */
	public static function getInstance($id = 'Joomla', $exceptions = true)
	{
		if (empty(self::$instances[$id]))
		{
			self::$instances[$id] = new RdfHelperMailer;
		}

		return self::$instances[$id];
	}
}
