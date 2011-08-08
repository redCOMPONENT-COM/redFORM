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
 */
 
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport( 'joomla.application.component.model' );

/**
 * Order Model
 */
class RedformModelConfiguration extends JModel {
	
	/**
	* Save the configuration
	*/
	function store() 
	{
		$table =& JTable::getInstance('component');

		$parampost['params'] = JRequest::getVar('params');
		$parampost['option'] = 'com_redform';
		$table->loadByOption( 'com_redform' );
		$table->bind( $parampost );

		// save the changes
		if (!$table->store()) {
			RedeventError::raiseWarning( 500, $table->getError() );
			return false;
		}
		
		return true;
	}
}
?>
