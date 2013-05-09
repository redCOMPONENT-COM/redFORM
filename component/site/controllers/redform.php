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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Redform Controller
*/
class RedformControllerRedform extends RedformController {

	/**
	 * Method to display the view
	 *
	 * @access   public
	 */
	function __construct()
	{
		parent::__construct();
	}

	public function redeventvm()
	{
		/* Set a default view if none exists */
		JRequest::setVar('view', 'redform' );
		JRequest::setVar('layout', 'redform' );

		$view =& $this->getView('redform', 'html');
		$model =& $this->getModel('redform');
		$view->setModel($model, true);
		$view->display();
		//    parent::display();
	}


	/**
	 * Method to show a weblinks view
	 *
	 * @access	public
	 */
	public function redform()
	{
		/* Set a default view if none exists */
		JRequest::setVar('view', 'redform' );
		JRequest::setVar('layout', 'redform' );

		$view = $this->getView('redform', 'html');
		$view->display();
		//		parent::display();
	}

	/**
	 * save the posted form data.
	 *
	 */
	function save()
	{
		$mainframe = Jfactory::getApplication();
		$model = $this->getModel('redform');

		$result = $model->apisaveform();

		$referer = JRequest::getVar('referer');

		if (!$result)
		{
			if (!JRequest::getBool('ALREADY_ENTERED'))
			{
				$msg = JText::_('COM_REDFORM_Sorry_there_was_a_problem_with_your_submission') .': '. $model->getError();
			}

			$this->setRedirect($referer, $msg, 'error');
			$this->redirect();
		}

		if ($url = $model->hasActivePayment($result->submit_key))
		{
			$url = 'index.php?option=com_redform&controller=payment&task=select&key='.$result->submit_key;
			$this->setRedirect($url);
			$this->redirect();
		}

		if ($url = $model->getRedirect())
		{
			$this->setRedirect($url);
			$this->redirect();
		}
		else
		{
			echo $model->getNotificationText();
		}
	}
}
