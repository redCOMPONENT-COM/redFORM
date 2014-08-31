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
 * Form table.
 *
 * @package     Redform.Backend
 * @subpackage  Tables
 * @since       1.5
 */
class RedformTableForm extends RTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'rwf_forms';

	/**
	 * @var  integer
	 */
	public $id;

	/**
	 * @var  string
	 */
	public $formname;

	/**
	 * @var  string
	 */
	public $startdate = '0000-00-00 00:00:00';

	/**
	 * @var  string
	 */
	public $enddate = '0000-00-00 00:00:00';

	/**
	 * @var  integer
	 */
	public $published = 1;

	/**
	 * @var  integer
	 */
	public $checked_out = null;

	/**
	 * @var  string
	 */
	public $checked_out_time = '0000-00-00 00:00:00';

	/**
	 * @var  string
	 */
	public $submissionsubject;

	/**
	 * @var  string
	 */
	public $submissionbody;

	/**
	 * @var  int
	 */
	public $showname = 0;

	/**
	 * @var  string
	 */
	public $classname;

	/**
	 * @var  int
	 */
	public $contactpersoninform = 0;

	/**
	 * @var  string
	 */
	public $contactpersonemail;

	/**
	 * @var  int
	 */
	public $contactpersonfullpost = 0;

	/**
	 * @var  int
	 */
	public $submitterinform = 0;

	/**
	 * @var  int
	 */
	public $submitnotification = 0;

	/**
	 * @var  string
	 */
	public $redirect;

	/**
	 * @var  string
	 */
	public $notificationtext;

	/**
	 * @var  int
	 */
	public $formexpires = 1;

	/**
	 * @var  int
	 */
	public $captchaactive = 0;

	/**
	 * @var  int
	 */
	public $access = 0;

	/**
	 * @var  int
	 */
	public $activatepayment = 0;

	/**
	 * @var  int
	 */
	public $show_js_price = 1;

	/**
	 * @var  string
	 */
	public $currency;

	/**
	 * @var  string
	 */
	public $paymentprocessing;

	/**
	 * @var  string
	 */
	public $paymentaccepted;

	/**
	 * @var  string
	 */
	public $contactpaymentnotificationsubject;

	/**
	 * @var  string
	 */
	public $contactpaymentnotificationbody;

	/**
	 * @var  string
	 */
	public $submitterpaymentnotificationsubject;

	/**
	 * @var  string
	 */
	public $submitterpaymentnotificationbody;

	/**
	 * @var  string
	 */
	public $cond_recipients;

	/**
	 * Field name to publish/unpublish/trash table registers. Ex: state
	 *
	 * @var  string
	 */
	protected $_tableFieldState = 'published';

	/**
	 * Constructor
	 *
	 * @param   JDatabase  &$db  A database connector object
	 *
	 * @throws  UnexpectedValueException
	 */
	public function __construct(&$db)
	{
		parent::__construct($db);

		// Some default values
		$this->contactpaymentnotificationsubject = JText::_('COM_REDFORM_PAYMENT_CONTACT_NOTIFICATION_EMAIL_SUBJECT_DEFAULT');
		$this->contactpaymentnotificationbody = JText::_('COM_REDFORM_PAYMENT_CONTACT_NOTIFICATION_EMAIL_BODY_DEFAULT');
		$this->submitterpaymentnotificationsubject = JText::_('COM_REDFORM_PAYMENT_SUBMITTER_NOTIFICATION_EMAIL_SUBJECT_DEFAULT');
		$this->submitterpaymentnotificationbody = JText::_('COM_REDFORM_PAYMENT_SUBMITTER_NOTIFICATION_EMAIL_BODY_DEFAULT');
	}

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link	http://docs.joomla.org/JTable/delete
	 * @since   11.1
	 */
	public function delete($pk = null)
	{
		if (!parent::delete($pk))
		{
			return false;
		}

		// Delete associated fields and rwf_form_x table
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->delete();
		$query->from('#__rwf_form_field');
		$query->where('form_id = ' . $pk);

		$db->setQuery($query);

		if (!$res = $db->query())
		{
			$this->setError($db->getErrorMsg());

			return false;
		}

		$q = "DROP TABLE #__rwf_forms_" . $pk;
		$db->setQuery($q);

		if (!$res = $db->query())
		{
			$this->setError($db->getErrorMsg());

			return false;
		}

		return true;
	}
}
