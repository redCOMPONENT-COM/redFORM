<?php
/**
 * @package    Redform.Site
 *
 * @copyright  Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Front-end Controller
 *
 * @package  Redform.Site
 * @since    1.5
 */
class RedformController extends JControllerLegacy
{
	/**
	 * Display override
	 *
	 * @param   boolean     $cachable   If true, the view output will be cached
	 * @param   array|bool  $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}
	 *
	 * @return  JController  A JController object to support chaining.
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$input = JFactory::getApplication()->input;

		// Set a default view if none exists
		if (!$input->getCmd('view', ''))
		{
			$this->redirect('index.php', 'Something went wrong', 'error');
		}

		return parent::display($cachable, $urlparams);
	}
}
