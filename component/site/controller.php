<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license GNU/GPL, see LICENSE.php
 * redFORM can be downloaded from www.redcomponent.com
 * redFORM is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redFORM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redFORM; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Front-end controller
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Front-end Controller
 */
class RedformController extends JController
{
	/**
	 * Method to show a weblinks view
	 *
	 * @access	public
	 */
	function display() {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			JRequest::setVar('view', 'redform' );
			JRequest::setVar('layout', 'redform' );
		}
		return;
		parent::display();
	}
	
  /**
   * loads the js file for redform price, making it possible to use JText
   */
	function jsprice()
	{
		header('Content-type: text/javascript');
  	require_once(JPATH_SITE.DS.'components'.DS.'com_redform'.DS.'assets'.DS.'js'.DS.'formprice.js');
  	die();
	}
	
  /**
   * loads the js file for redform form validation, making it possible to use JText 
   */
	function jscheck()
	{
		header('Content-type: text/javascript');
  	require_once(JPATH_SITE.DS.'components'.DS.'com_redform'.DS.'assets'.DS.'js'.DS.'formcheck.js');
  	die();
	}
}
?>