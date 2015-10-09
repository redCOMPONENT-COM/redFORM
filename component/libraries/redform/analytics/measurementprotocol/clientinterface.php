<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Analytics
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * google Measurement protocol api client interface
 *
 * @package     Redform.Libraries
 * @subpackage  Analytics
 * @since       2.5
 */
interface RdfAnalyticsMeasurementprotocolClientinterface
{
	/**
	 * generate hit
	 *
	 * @param   array  $data  data to use for hit
	 *
	 * @return mixed
	 */
	public function hit($data);
}
