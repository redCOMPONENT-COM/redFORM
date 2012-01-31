<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage redFORM
 * @copyright redFORM (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
// Import library dependencies
jimport('joomla.plugin');

// load language file for frontend
JPlugin::loadLanguage( 'plg_redform_mailing_ccnewsletter', JPATH_ADMINISTRATOR );

class plgRedform_mailingCcnewsletter extends JPlugin {
 	
	public function plgRedform_mailingCcnewsletter(&$subject, $config = array()) 
	{
		parent::__construct($subject, $config);
	}

	function getIntegrationName(&$names)
	{
		$names[] = 'ccnewsletter';
		return true;
	}
	
	function subscribe($integration, $subscriber, $listname)
	{			
		if (strtolower($integration) != 'ccnewsletter') {
			return true;
		}
		
		$db = &JFactory::getDBO();
 		$fullname        = $subscriber->name;
 		$submitter_email = $subscriber->email;		
 				
 		if (file_exists(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_ccnewsletter' . DS . 'tables' .DS .'subscriber.php'))
 		{
 			/* ccNewsletter is installed, let's add the user */
 			$query = ' SELECT id ' 
 			       . ' FROM #__ccnewsletter_subscribers ' 
 			       . ' WHERE email = ' . $db->Quote($submitter_email)
 			       ;
 			$db->setQuery($query);
 			$res = $db->loadResult();
 			
 			if ($res) { // already subscribed
 				return true;
 			}
 			
 			require_once( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_ccnewsletter' . DS . 'tables' .DS .'subscriber.php');
 			$ccsubscriber = &JTable::getInstance('subscriber', 'RedformTable');
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
