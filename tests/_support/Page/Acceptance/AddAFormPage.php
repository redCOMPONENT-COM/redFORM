<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance;

class AddAFormPage extends RedFormAdminPage
{
	/**
	 * @var string
	 */
	public static $url = "administrator/index.php?option=com_redform&view=forms";

	/**
	 * @var string
	 */
	public static $form = "Forms";

	/**
	 * @var string
	 */
	public static $formField = "Form field";

	/**
	 * @var string
	 */
	public static $formName = "Form name";

	/**
	 * @var string
	 */
	public static $formNameLbl = "#jform_formname-lbl";

	/**
	 * @var string
	 */
	public static $formNameId = "#jform_formname";

	/**
	 * @var string
	 */
	public static $fields = "//li/a[normalize-space(text()) = \"Fields\"]";

	/**
	 * @var string
	 */
	public static $confirmation = "//li/a[normalize-space(text()) = \"Confirmation\"]";

	/**
	 * @var string
	 */
	public static $fieldId = "jform_field_id";

	/**
	 * @var string
	 */
	public static $sectionId = "jform_section_id";

	/**
	 * @var string
	 */
	public static $formExpires = "Form Expires";

	/**
	 * @var string
	 */
	public static $formExpiresLbl = "//label[@id='jform_formexpires-lbl']";

	/**
	 * @var string
	 */
	public static $startDateLbl = "//label[@id='jform_startdate-lbl']";

	/**
	 * @var string
	 */
	public static $startDate = "//input[@id='jform_startdate']";

	/**
	 * @var string
	 */
	public static $endDateLbl = "//label[@id='jform_enddate-lbl']";

	/**
	 * @var string
	 */
	public static $endDate = "//input[@id='jform_enddate']";

	/**
	 * @var string
	 */
	public static $confirmationNotificationEmailSubjectLbl = "//label[@id='jform_confirmation_contactperson_subject-lbl']";

	/**
	 * @var string
	 */
	public static $confirmationNotificationEmailSubject = "//input[@id='jform_confirmation_contactperson_subject']";

	/**
	 * @var string
	 */
	public static $confirmationNotificationEmailBodyLbl = "//label[@id='jform_confirmation_contactperson_body-lbl']";

	/**
	 * @var string
	 */
	public static $confirmationNotificationEmailBody = "//textarea[@id='jform_confirmation_contactperson_body']";

	/**
	 * @var string
	 */
	public static $toggleEditorConfirmation = "//a[@onclick=\"tinyMCE.execCommand('mceToggleEditor', false, 'jform_confirmation_contactperson_body');return false;\"]";

	/**
	 * @var string
	 */
	public static $enableConfirmation = "Enable confirmation";

	/**
	 * @var string
	 */
	public static $enableConfirmationNotification = "Enable confirmation notification";

	/**
	 * @var string
	 */
	public static $messageMissingFormName = "Field required: Form name";

	/**
	 * @var string
	 */
	public static $required = 'Required';

	/**
	 * @param $value
	 * @return string
	 */
	public static function formList($value)
	{
		$formList = "//a[normalize-space(text()) = \"' . $value . '\"]";

		return $formList;
	}

	/**
	 * @param $value
	 * @return string
	 */
	public function formEdit($value)
	{
		$formEdit = "//h1[contains(text(),'Form - $value')]";
		return $formEdit;
	}
}