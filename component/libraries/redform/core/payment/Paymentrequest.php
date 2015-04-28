<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Class RdfCore Submission payment request
 *
 * @package     Redform.Libraries
 * @subpackage  Helper
 * @since       2.5
 */
class RdfCorePaymentSubmissionpaymentrequest
{
	/**
	 * @var string
	 */
	protected $submitKey;

	/**
	 * Constructor
	 *
	 * @param   string  $submitKey  submit key
	 */
	public function __construct($submitKey)
	{
		$this->submitKey = $submitKey;
	}

	public function update()
	{
		// First get
	}
}
