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
 * Class plgRedform_mailingPhplist
 *
 * @package     Redform.plugins
 * @subpackage  mailing
 * @since       2.5
 */
class plgRedform_mailingPhplist extends JPlugin
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
		$names[] = 'Phplist';

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
		if (strtolower($integration) != 'phplist')
		{
			return true;
		}

		$db = JFactory::getDBO();
		$fullname = $subscriber->name;
		$submitter_email = $subscriber->email;

		$path = $this->params->get('phplist_path');

		if (JFolder::exists(JPATH_SITE . '/' . $path))
		{
			/* Include the PHPList API */
			require_once 'phplist/phplistuser.php';
			require_once 'phplist/simpleemail.php';
			require_once 'phplist/query.php';
			require_once 'phplist/errorhandler.php';

			/* Get the PHPList path configuration */
			PhpListUser::$PHPListPath = JPATH_SITE . '/' . $path;

			$user = new PhpListUser;
			$user->set_email($submitter_email);
			$listid = $user->getListId($listname);
			$user->addListId($listid);
			$user->save();
		}

		return true;
	}
}
