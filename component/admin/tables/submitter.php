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
 * Submitter table.
 *
 * @package     Redshopb.Backend
 * @subpackage  Tables
 * @since       2.5
 */
class RedformTableSubmitter extends RTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'rwf_submitters';

	/** @var int Primary key */
	public $id = null;

	/** @var string The form ID */
	public $form_id = null;

	/** @var string Date and time form was submitted */
	public $submission_date = null;

	/** @var string integration key */
	public $integration 	= null;

	/** @var int The cross reference ID of the redEVENT event/venue/date */
	public $xref = null;

	/** @var int key to specific form table entry */
	public $answer_id = null;

	/** @var bool If the submitter wants to be signed up to the newsletter  */
	public $submitternewsletter = null;

	/** @var string Holds the serialized post data from the forms  */
	public $rawformdata = null;

	/** @var int Unique key that identifies single registrations */
	public $submit_key = null;

	public $uniqueid = null;

	public $price = null;

	public $currency = null;
}
