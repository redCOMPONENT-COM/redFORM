<?php
/**
 * @package     Redform.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Submission price items table.
 *
 * @package     Redform.Backend
 * @subpackage  Tables
 * @since       2.5
 */
class RedformTableSubmissionpriceitem extends RTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'rwf_submission_price_item';

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * This method checks that the parent_id is non-zero and exists in the database.
	 * Note that the root node (parent_id = 0) cannot be manipulated with this class.
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function check()
	{
		if (!parent::check())
		{
			return false;
		}

		if (empty($this->sku))
		{
			$this->setError('COM_REDFORM_SUBMISSION_ITEM_SKU_IS_REQUIRED');

			return false;
		}

		return true;
	}
}
