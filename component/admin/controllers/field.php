<?php
/**
 * @package     Redform.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Form Controller
 *
 * @package     Redform.Backend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedformControllerField extends RdfControllerForm
{
	/**
	 * Add field
	 *
	 * @return void
	 */
	public function add()
	{
		$app = JFactory::getApplication();
		$app->setUserState('com_redform.global.field.type', '');

		return parent::add();
	}

	/**
	 * Edit field
	 *
	 * @param   int     $key     [description]
	 * @param   string  $urlVar  [description]
	 *
	 * @return void
	 */
	public function edit($key = null, $urlVar = null)
	{
		$app = JFactory::getApplication();
		$fieldModel = RModel::getAdminInstance('Field');

		$field = $fieldModel->getItem();
		$app->setUserState('com_redform.global.field.type', $field->type);

		return parent::edit($key, $urlVar);
	}

	/**
	 * For auto-submit form when client choose type
	 *
	 * @return void
	 */
	public function setType()
	{
		$app = JFactory::getApplication();
		$recordId = $app->input->get('id', 0, 'int');
		$data = $app->input->get('jform', array(), 'array');

		$app->setUserState('com_redform.edit.field.data', $data);
		$app->setUserState('com_redform.global.field.type', $data['type']);

		$redirect = JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId), false);

		$this->setRedirect($redirect);
	}
}
