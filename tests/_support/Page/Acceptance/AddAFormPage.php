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
}