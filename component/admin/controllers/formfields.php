<?php
/**
 * @package     Redform.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Fields Controller
 *
 * @package     Redform.Backend
 * @subpackage  Controllers
 * @since       1.5
 */
class RedformControllerFormfields extends RControllerAdmin
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws  Exception
	 *
	 * @since    __deploy_version__
	 */
	public function __construct(array $config = array())
	{
		parent::__construct($config);

		// Value = 0
		$this->registerTask('unsetRequired', 'setRequired');
	}

	/**
	 * Method to set required state of a list of items
	 *
	 * @return  void
	 */
	public function setRequired()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		$value = $this->getTask() == 'setRequired' ? 1 : 0;

		if (empty($cid))
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			ArrayHelper::toInteger($cid);

			// Publish the items.
			try
			{
				if ($model->setRequired($cid, $value))
				{
					switch ($this->getTask())
					{
						case 'setRequired':
							$ntext = $this->text_prefix . '_N_ITEMS_SET_REQUIRED';
							break;

						case 'unsetRequired':
							$ntext = $this->text_prefix . '_N_ITEMS_SET_UNREQUIRED';
							break;
					}

					$this->setMessage(JText::plural($ntext, count($cid)));
				}
				else
				{
					$this->setMessage($model->getError(), 'error');
				}
			}
			catch (Exception $e)
			{
				$this->setMessage(JText::_('JLIB_DATABASE_ERROR_ANCESTOR_NODES_LOWER_STATE'), 'error');
			}
		}

		$extension = $this->input->get('extension');
		$extensionURL = ($extension) ? '&extension=' . $extension : '';

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute($extensionURL));
	}
}
