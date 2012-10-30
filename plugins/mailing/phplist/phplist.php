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

class plgRedform_mailingPhplist extends JPlugin {
 	
	public function plgRedform_mailingPhplist(&$subject, $config = array()) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	function getIntegrationName(&$names)
	{
		$names[] = 'Phplist';
		return true;
	}
	
	function subscribe($integration, $subscriber, $listname)
	{	
		if (strtolower($integration) != 'phplist') {
			return true;
		}
		
		$db = &JFactory::getDBO();
 		$fullname        = $subscriber->name;
 		$submitter_email = $subscriber->email;		
 		
 		$path = $this->params->get('phplist_path');

 		if (JFolder::exists(JPATH_SITE.DS.$path)) 
 		{
 			/* Include the PHPList API */
 			require_once('phplist'.DS.'phplistuser.php');
 			require_once('phplist'.DS.'simpleemail.php');
 			require_once('phplist'.DS.'query.php');
 			require_once('phplist'.DS.'errorhandler.php');

 			/* Get the PHPList path configuration */
 			PhpListUser::$PHPListPath = JPATH_SITE.DS.$path;

 			$user = new PhpListUser();
 			$user->set_email($submitter_email);
 			$listid = $user->getListId($listname);
 			$user->addListId($listid);
 			$user->save();
 		}
 		
 		return true;
	}
}
?>
