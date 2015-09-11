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
class RedformControllerForm extends RdfControllerForm
{
	/**
	 * Ajax call to get fields tab content.
	 *
	 * @return  void
	 */
	public function ajaxfields()
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		$formId = $input->getInt('id');

		if ($formId)
		{
			$model = RModelAdmin::getInstance('Formfields', 'RedformModel');
			$model->setState('filter.form_id', $formId);

			$formName = 'fieldsForm';
			$pagination = $model->getPagination();
			$pagination->set('formName', $formName);

			echo RdfLayoutHelper::render('form.formfields', array(
					'state' => $model->getState(),
					'items' => $model->getItems(),
					'pagination' => $pagination,
					'filter_form' => $model->getForm(),
					'activeFilters' => $model->getActiveFilters(),
					'formName' => $formName,
					'showToolbar' => true,
					'action' => 'index.php?option=com_redform&view=form&model=formfields',
					'return' => base64_encode('index.php?option=com_redform&view=form&layout=edit&id='
						. $formId . '&tab=fields&from_form=1')
				)
			);
		}

		$app->close();
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key
	 *                         (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if access level check and checkout passes, false otherwise.
	 */
	public function edit($key = null, $urlVar = null)
	{
		$cid   = $this->input->post->get('cid', array(), 'array');

		// Get the previous record id (if any) and the current record id.
		$recordId = (int) (count($cid) ? $cid[0] : $this->input->getInt('id'));

		if ($recordId)
		{
			$model = $this->getModel();
			$model->checkFields($recordId);
		}

		return parent::edit($key, $urlVar);
	}
}
