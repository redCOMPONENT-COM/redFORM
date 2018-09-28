<?php
/**
 * @package     Redform.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Forms Controller
 *
 * @package     Redform.Backend
 * @subpackage  Controllers
 * @since       1.5
 */
class RedformControllerForms extends RControllerAdmin
{
	/**
	 * Copy item(s)
	 *
	 * @return  void
	 */
	public function copy()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		// Get the model.
		$model = $this->getModel();

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);

			// Copy the items.
			if ($model->copy($cid))
			{
				$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_COPIED', count($cid)));
			}
			else
			{
				$this->setMessage($model->getError(), 'error');
			}
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}

	public function autodelete()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get forms to clean
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		try {
			$helper = new \Redform\Helper\Autodelete;
			$helper->process($cid);
			$this->setMessage(JText::plural($this->text_prefix . '_N_FORMS_SUBMITTERS_AUTODELETED', count($cid)));
		}
		catch (\Exception $e) {
			RdfHelperLog::simpleLog($e->getMessage());
			$this->setMessage($e->getMessage(), 'error');
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}
}
