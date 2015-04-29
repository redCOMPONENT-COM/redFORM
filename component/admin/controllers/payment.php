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
 * Payment Controller
 *
 * @package     Redform.Backend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedformControllerPayment extends RdfControllerForm
{
	/**
	 * Function that allows child controller access to model data
	 * after the data has been saved.
	 *
	 * @param   object  &$model     The data model object.
	 * @param   array   $validData  The validated data.
	 *
	 * @return  void
	 */
	protected function postSaveHook(&$model, $validData = array())
	{
		parent::postSaveHook($model, $validData);

		if ($validData['paid'] == 1)
		{
			$model->setPaymentRequestsAsPaid();
		}
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);

		$prid = $this->input->get('pr');

		if ($prid)
		{
			$append .= '&pr=' . $prid;
		}

		return $append;
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToListAppend()
	{
		$append = parent::getRedirectToListAppend();

		$prid = $this->input->get('pr');

		if ($prid)
		{
			$append .= '&pr=' . $prid;
		}

		return $append;
	}
}
