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
JPlugin::loadLanguage( 'plg_redform_mailing_acajoom', JPATH_ADMINISTRATOR );

class plgRedform_mailingAcajoom extends JPlugin {
 	
	public function plgRedform_mailingAcajoom(&$subject, $config = array()) 
	{
		parent::__construct($subject, $config);
	}

	function getIntegrationName(&$names)
	{
		$names[] = 'Acajoom';
		return true;
	}
	
	function subscribe($integration, $subscriber, $listname)
	{	
		if (strtolower($integration) != 'acajoom') {
			return false;
		}
		require_once('acajoom'.DS.'acajoom_queue.php');
		require_once('acajoom'.DS.'acajoom_subscribers.php');
		
		$db = &JFactory::getDBO();
 		$fullname        = $subscriber->name;
 		$submitter_email = $subscriber->email;
		
 		/* Check if Acajoom is installed */
 		$q = "SELECT COUNT(id) FROM #__components WHERE link = 'option=com_acajoom'";
 		$db->setQuery($q);
 		
 		if ($db->loadResult() > 0) 
 		{
 			/* Acajoom is installed, let's add the user */
 			$acajoomsubscriber = JTable::getInstance('acajoom_subscribers', 'Table');
 			
 			$myid = JFactory::getUser();
 			if (!isset($myid->id)) $myid->id = 0;
 			$acajoomsettings = array('user_id' => $myid->id,
                              'name' => $fullname,
                              'email' => $submitter_email,
                              'subscribe_date' => date('Y-m-d H:i:s'));
 			 			
 			if (!$acajoomsubscriber->find($submitter_email)) // email already registered in acajoom ?
 			{
 				$acajoomsubscriber->bind($acajoomsettings);		 						
 				
 				if (!$acajoomsubscriber->store()) 
 				{
 					if (stristr($db->getErrorMsg(), 'duplicate entry')) {
 						$mainframe->enqueueMessage(JText::_('PLG_REDFORM_MAILING_ACAJOOM_ALREADY_REGISTERED'), 'error');
 					}
 					else $mainframe->enqueueMessage(JText::_('PLG_REDFORM_MAILING_ACAJOOM_SUBSCRIBE_ERROR').' '.$db->getErrorMsg(),'error');
 				}
 			}
 			
 			/* Check if the mailinglist exists, add the user to it */
 			$list = false;
 			$q = "SELECT id, acc_id FROM #__acajoom_lists WHERE list_name = ".$db->Quote($listname)." LIMIT 1";
 			$db->setQuery($q);
 			$list = $db->loadObject();

 			if ($db->getAffectedRows() > 0) 
 			{
 				/* Load the queue table */
 				$acajoomqueue = JTable::getInstance('acajoom_queue', 'Table');

 				/* Collect subscriber details */
 				$queue = new stdClass;
 				$queue->id = 0;	
 				$queue->subscriber_id = $acajoomsubscriber->id;
 				$queue->list_id = $list->id;
 				$queue->type = 1;
 				$queue->mailing_id = 0;
 				$queue->send_date = '0000-00-00 00:00:00';
 				$queue->suspend = 0;
 				$queue->delay = 0;
 				$queue->acc_level = $list->acc_id;
 				$queue->issue_nb = 0;
 				$queue->published = 0;
 				$queue->params = '';

 				$acajoomqueue->bind($queue);
 				
 				if (!$acajoomqueue->check()) {
 					JError::raiseWarning(0, $acajoomqueue->getError());
 				}
 				else 
 				{
 					if (!$acajoomqueue->store()) {
 						JError::raiseWarning(0, $acajoomqueue->getError());								
 					}
 				}
 			}
 		}
 		return true;
	}
}
?>
