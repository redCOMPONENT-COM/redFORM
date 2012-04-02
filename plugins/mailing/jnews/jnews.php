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
jimport('joomla.plugin.plugin');

class plgRedform_mailingJnews extends JPlugin {
 	
	public function __construct(&$subject, $config = array()) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	function getIntegrationName(&$names)
	{
		$names[] = 'jnews';
		return true;
	}
	
	function subscribe($integration, $subscriber, $listname)
	{	
		if (strtolower($integration) != 'jnews') {
			return false;
		}
		
 		if (!JComponentHelper::isEnabled('com_jnews')) {
 			return false;
 		}
 		$mainframe = JFactory::getApplication();
 		
		require_once('lib'.DS.'jnews_queue.php');
		require_once('lib'.DS.'jnews_subscribers.php');
		
		$db = &JFactory::getDBO();
 		$fullname        = $subscriber->name;
 		$submitter_email = $subscriber->email;
 		
 			/* jnews is installed, let's add the user */
 			$jnewssubscriber = JTable::getInstance('jnews_subscribers', 'Table');
 			
 			$myid = JFactory::getUser();
 			if (!isset($myid->id)) $myid->id = 0;
 			$jnewssettings = array('user_id' => $myid->id,
                              'name' => $fullname,
                              'email' => $submitter_email,
                              'subscribe_date' => time());
 			if (!$jnewssubscriber->find($submitter_email)) // email already registered in jnews ?
 			{
 				$jnewssubscriber->bind($jnewssettings);		 						
 				
 				if (!$jnewssubscriber->store()) 
 				{
 					if (stristr($db->getErrorMsg(), 'duplicate entry')) {
 						$mainframe->enqueueMessage(JText::_('PLG_REDFORM_MAILING_jnews_ALREADY_REGISTERED'), 'error');
 					}
 					else $mainframe->enqueueMessage(JText::_('PLG_REDFORM_MAILING_jnews_SUBSCRIBE_ERROR').' '.$db->getErrorMsg(),'error');
 				}
 			}
 			
 			/* Check if the mailinglist exists, add the user to it */
 			$list = false;
 			$q = "SELECT id, acc_id FROM #__jnews_lists WHERE list_name = ".$db->Quote($listname);
 			$db->setQuery($q);
 			$list = $db->loadObject();

 			if ($list) 
 			{ 				
 				// add to subscriber list table
 				$query = $db->getQuery(true); 				
 				$query->select('list_id');
 				$query->from('#__jnews_listssubscribers');
 				$query->where('list_id = '.$list->id);
 				$query->where('subscriber_id = '.$jnewssubscriber->id);
 				$db->setQuery($query);
 				$res = $db->loadResult();
 				if ($res) {
 					// already susbscribed to this list
 					return true;
 				}
 				
 				$query = ' INSERT INTO #__jnews_listssubscribers SET ' 
 				       . ' list_id = '.$list->id
 				       . ' , subscriber_id = '.$jnewssubscriber->id
 				       . ' , subdate = '.time()
 				;
 				$db->setQuery($query);
 				if (!$db->query()) {
 					$mainframe->enqueueMessage(JText::_('PLG_REDFORM_MAILING_jnews_SUBSCRIBE_ERROR').' '.$db->getErrorMsg(),'error');
 				}
 			}
 		return true;
	}
}
?>
