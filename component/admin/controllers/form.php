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

			echo RdfHelperLayout::render('form.formfields', array(
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
}
