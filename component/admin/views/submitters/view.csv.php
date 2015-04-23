<?php
/**
 * @package     Redform.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Fields View
 *
 * @package     Redform.Backend
 * @subpackage  Views
 * @since       2.5
 */
class RedformViewSubmitters extends RViewCsv
{
	/**
	 * Get the columns for the csv file.
	 *
	 * @return  array  An associative array of column names as key and the title as value.
	 */
	protected function getColumns()
	{
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_redform');

		$this->formInfo = $this->get('FormInfo');

		$columns = array(
			'submission_date' => JText::_('COM_REDFORM_SUBMISSION_DATE'),
			'formname' => JText::_('COM_REDFORM_FORM_NAME'),
		);

		if ($this->formInfo->enable_confirmation)
		{
			$columns['confirmed_date'] = JText::_('COM_REDFORM_confirmed_HEADER');
			$columns['confirmed_type'] = JText::_('COM_REDFORM_confirmed_type_HEADER');
		}

		if ($app->input->get('integration', '') && $params->get('showintegration', false))
		{
			$columns['integration'] = JText::_('COM_REDFORM_SUBMISSION_DATE');
		}

		$fields = $this->get('fields');

		foreach ($fields as $field)
		{
			$columns['field_' . $field->field_id] = $field->field_header;
		}

		if ($this->formInfo->activatepayment)
		{
			$columns['price'] = JText::_('COM_REDFORM_PRICE');
			$columns['currency'] = JText::_('COM_REDFORM_PAYMENTCURRENCY');
			$columns['status'] = JText::_('COM_REDFORM_REGISTRATION_PAID');
		}

		return $columns;
	}
}
