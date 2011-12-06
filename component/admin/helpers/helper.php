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

class RedformHelper {
	
  public static function setMenu()
  {
    $user = & JFactory::getUser();
    $view = JRequest::getVar('view', '');
    $controller = JRequest::getVar('controller', '');
    //Create Submenu
    JSubMenuHelper::addEntry( JText::_('COM_REDFORM_FORMS' ), 'index.php?option=com_redform&view=forms', $view == '' || $view == 'forms');
    JSubMenuHelper::addEntry( JText::_('COM_REDFORM_FIELDS' ), 'index.php?option=com_redform&view=fields', $view == 'fields');
    JSubMenuHelper::addEntry( JText::_('COM_REDFORM_SUBMITTERS' ), 'index.php?option=com_redform&view=submitters', $view == 'submitters');
    JSubMenuHelper::addEntry( JText::_('COM_REDFORM_LOGS' ), 'index.php?option=com_redform&view=log', $view == 'log');
  }
}