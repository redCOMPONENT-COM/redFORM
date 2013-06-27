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

class plgRedform_mailingAcymailing extends JPlugin {
 	
	public function plgRedform_mailingAcymailing(&$subject, $config = array()) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	function getIntegrationName(&$names)
	{
		$names[] = 'acymailing';
		return true;
	}
	
	function subscribe($integration, $subscriber, $listname)
	{	
		$app = & JFactory::getApplication();
		
		if (strtolower($integration) != 'acymailing') {
			return true;
		}
		
		$db = &JFactory::getDBO();
 		$fullname        = $subscriber->name;
 		$submitter_email = $subscriber->email;

 		$lists = $this->getLists();
 		
 		$listid = 0;
 		foreach ($lists as $l)
 		{
 			if ($l->name == $listname) {
 				$listid = $l->listid;
 				break;
 			}
 		}
 		if (!$listid) {
 			$app->enqueueMessage(JText::_('PLG_REDFORM_MAILING_ACYMAILING_LIST_NOT_FOUND'), 'error');
 			return false;
 		}
 		
 		// first, add user to acymailing
 		$myUser = new stdclass();
		$myUser->email = $subscriber->email;
		$myUser->name  = $subscriber->name;
		
		$subscriberClass = acymailing::get('class.subscriber');
		
		$subid = $subscriberClass->save($myUser); //this function will return you the ID of the user inserted in the AcyMailing table
 		
		// then add to the mailing list
 		$subscribe = array($listid);
 		$memberid  = $subid;
 		
 		$newSubscription = array();
 		if (!empty($subscribe))
 		{
 			foreach ($subscribe as $listId)
 			{
 				$newList = null;
 				$newList['status'] = 1;
 				$newSubscription[$listId] = $newList;
 			}
 		}

 		if (empty($newSubscription)) return; //there is nothing to do...

 		$subscriberClass->saveSubscription($subid,$newSubscription);
 				
 		return true;
	}
	
	function getLists()
	{
		$app = & JFactory::getApplication();
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php'))
		{
 			$app->enqueueMessage(JText::_('PLG_REDFORM_MAILING_ACYMAILING_NOT_FOUND'), 'error');
 			return false;
		}
		
		$listClass = acymailing::get('class.list');
		
		$allLists = $listClass->getLists();
		return $allLists;
	}
}
?>
