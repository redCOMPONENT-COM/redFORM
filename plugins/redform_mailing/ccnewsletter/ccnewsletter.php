<?php
/**
 * @package     Redform.plugins
 * @subpackage  mailing
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * Class plgRedform_mailingCcnewsletter
 *
 * @package     Redform.plugins
 * @subpackage  mailing
 * @since       2.5
 */
class plgRedform_mailingCcnewsletter extends JPlugin
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
	 * Add to integration names
	 *
	 * @param   array  &$names  names to add to
	 *
	 * @return bool
	 */
	public function getIntegrationName(&$names)
	{
		$names[] = 'ccnewsletter';

		return true;
	}

	/**
	 * Subscribe to list
	 *
	 * @param   string  $integration  integration name
	 * @param   JUser   $subscriber   subscriber
	 * @param   object  $listname     list name
	 *
	 * @return bool
	 */
	public function subscribe($integration, $subscriber, $listname)
	{
		if (strtolower($integration) != 'ccnewsletter')
		{
			return true;
		}

		$db = JFactory::getDBO();
		$fullname = $subscriber->name;
		$submitter_email = $subscriber->email;

		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_ccnewsletter/tables/subscriber.php'))
		{
			/* ccNewsletter is installed, let's add the user */
			$query = ' SELECT id '
				. ' FROM #__ccnewsletter_subscribers '
				. ' WHERE email = ' . $db->Quote($submitter_email);
			$db->setQuery($query);
			$res = $db->loadResult();

			if ($res)
			{
				// Already subscribed
				return true;
			}

			require_once JPATH_ADMINISTRATOR . '/components/com_ccnewsletter/tables/subscriber.php';
			$ccsubscriber = JTable::getInstance('subscriber', 'RedformTable');
			$ccsettings = array('name' => $fullname,
				'email' => $submitter_email,
				'plainText' => '0',
				'enabled' => '1',
				'sdate' => date('Y-m-d H:i:s'));
			$ccsubscriber->bind($ccsettings);
			$ccsubscriber->store();
		}

		return true;
	}
}
