<?php
/**
 * @package    Redform.Admin
 *
 * @copyright  Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * redFORM Component Controller
 *
 * @package  Redform.Admin
 * @since    1.5
 */
class RedformController extends JControllerLegacy
{
	/**
	 * Typical view method for MVC based architecture
	 *
	 * This function is provide as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return JControllerLegacy A JControllerLegacy object to support chaining.
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$input = JFactory::getApplication()->input;
		$input->set('view', $input->get('view', 'forms'));
		$input->set('task', $input->get('task', 'display'));

		return parent::display($cachable, $urlparams);
	}

	/**
	 * Clears log file
	 *
	 * @return void
	 */
	public function clearlog()
	{
		RedformHelperLog::clear();
		$msg = JText::_('COM_REDFORM_LOG_CLEARED');
		$this->setRedirect('index.php?option=com_redform&view=log', $msg);
		$this->redirect();
	}
}
