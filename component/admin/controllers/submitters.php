<?php
/**
 * @package     Redform.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * Submitters Controller
 *
 * @package     Redform.Backend
 * @subpackage  Controllers
 * @since       1.5
 */
class RedformControllerSubmitters extends RControllerAdmin
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws  Exception
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('forcedelete',  'delete');
	}

	/**
	 * confirm a submission
	 *
	 * @return void
	 */
	public function confirm()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		$model = $this->getModel();

		// Confirm the items.
		if ($model->confirm($cid))
		{
			$this->setMessage(JText::plural($this->text_prefix . '_N_SUBMITTERS_CONFIRMED', count($cid)));

			JPluginHelper::importPlugin('redform');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onConfirm', array('admin', &$cid));
		}
		else
		{
			$this->setMessage($model->getError(), 'error');
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}

	/**
	 * unconfirm a submission
	 *
	 * @return void
	 */
	public function unconfirm()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		$model = $this->getModel();

		// Confirm the items.
		if ($model->unconfirm($cid))
		{
			$this->setMessage(JText::plural($this->text_prefix . '_N_SUBMITTERS_UNCONFIRMED', count($cid)));

			JPluginHelper::importPlugin('redform');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onUnconfirm', array('admin', &$cid));
		}
		else
		{
			$this->setMessage($model->getError(), 'error');
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}

	/**
	 * Send the notification email again
	 *
	 * @return void
	 */
	public function resendNotification()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		$rfcore = new RdfCore;
		$sent = 0;

		foreach ($cid as $sid)
		{
			$answers = $rfcore->getSidAnswers($sid);

			if ($answers->sendSubmitterNotification())
			{
				$sent++;
			}
		}

		if ($sent == count($cid))
		{
			$msg = JText::sprintf('COM_REDFORM_D_CONFIRMATION_EMAIL_RESENT', count($cid));

			// Set redirect
			$this->setRedirect($this->getRedirectToListRoute(), $msg, 'success');
		}
		elseif ($sent)
		{
			$msg = JText::sprintf('COM_REDFORM_D_CONFIRMATION_EMAIL_RESENT_NOTICE', count($cid));

			// Set redirect
			$this->setRedirect($this->getRedirectToListRoute(), $msg, 'notice');
		}
		else
		{
			// Set redirect
			$this->setRedirect($this->getRedirectToListRoute());
		}
	}

	/**
	 * Removes an item.
	 *
	 * @return  void
	 */
	public function delete()
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
			ArrayHelper::toInteger($cid);

			$force = $this->getTask() == 'forcedelete';

			// Remove the items.
			if ($model->delete($cid, $force))
			{
				$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid)));
			}
			else
			{
				$this->setMessage($model->getError(), 'error');
			}
		}

		// Invoke the postDelete method to allow for the child class to access the model.
		$this->postDeleteHook($model, $cid);

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}

	/**
	 * Turn a submission
	 *
	 * @return void
	 */
	public function turn()
	{
		// Get items to remove from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		foreach ($cid as $sid)
		{
			$helper = new RdfPaymentTurnsubmission($sid);
			$helper->turn();
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}
}
