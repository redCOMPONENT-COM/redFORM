<?php
/**
 * @version 1.0 $Id: view.html.php 165 2009-06-01 16:37:38Z julien $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * View class for the redevent log
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedFormViewLog extends JView {

	function display($tpl = null)
	{
		//initialise variables
		$document	= & JFactory::getDocument();
		$user 		= & JFactory::getUser();

		//build toolbar
		JToolBarHelper::title( JText::_( 'REDForm LOG' ), 'home' );
    JToolBarHelper::custom('clearlog', 'delete', 'delete', 'Clear Log', false);
    //create the toolbar

		// Get data from the model
		$log      = & $this->get( 'Data');

		//assign vars to the template
		$this->assignRef('log'		, $log);

		parent::display($tpl);
	}
}
?>