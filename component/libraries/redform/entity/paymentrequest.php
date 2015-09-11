<?php
/**
 * @package     Redform.Library
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Paymentrequest entity.
 *
 * @since  3.0
 */
class RdfEntityPaymentrequest extends RdfEntityBase
{
	/**
	 * Get submitter
	 *
	 * @return RdfEntitySubmitter
	 */
	public function getSubmitter()
	{
		$submitter = RdfEntitySubmitter::getInstance($this->submission_id);
		$submitter->loadItem();

		return $submitter;
	}
}
