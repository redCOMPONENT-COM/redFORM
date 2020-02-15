<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance;

class AddAFieldPage extends RedFormAdminPage
{
	/**
	 * @var string
	 */
	public static $URL = "administrator/index.php?option=com_redform&view=fields";

	/**
	 * @var string
	 */
	public static $field = "Fields";

	/**
	 * @var string
	 */
	public static $name = "Name";

	/**
	 * @var string
	 */
	public static $nameLbl = "#jform_field-lbl";

	/**
	 * @var string
	 */
	public static $nameId = "#jform_field";

	/**
	 * @var string
	 */
	public static $fieldHeader = "Field header";

	/**
	 * @var string
	 */
	public static $fieldHeaderLbl = "#jform_field_header-lbl";

	/**
	 * @var string
	 */
	public static $fieldHeaderId = "#jform_field_header";

	/**
	 * @var string
	 */
	public static $fieldType = "Field type";

	/**
	 * @var string
	 */
	public static $fieldTypeLbl = "#jform_fieldtype-lbl";

	/**
	 * @var string
	 * @since 3.3.28
	 */
	public static $fieldTypeID = "#jform_fieldtype_chzn";

	/**
	 * @var string
	 * @since 3.3.28
	 */
	public static $fieldTypeInput = "//div[@id='jform_fieldtype_chzn']//input";

	/**
	 * @var string
	 */
	public static $tooltip = "Tooltip";

	/**
	 * @var string
	 */
	public static $tooltipLbl = "#jform_tooltip-lbl";

	/**
	 * @var string
	 */
	public static $tooltipId = "#jform_tooltip";

	/**
	 * @var string
	 */
	public static $defaultValue = "Default value";

	/**
	 * @var string
	 */
	public static $defaultValueLbl = "#jform_default-lbl";

	/**
	 * @var string
	 */
	public static $defaultValueId = "#jform_default";

	/**
	 * @var string
	 * @since 3.3.28
	 */
	public static $showOnValueLb = "Show on";

	/**
	 * @var string
	 * @since 3.3.28
	 */
	public static $showOnValueID = "#jform_params_showon";

	/**
	 * @var string
	 */
	public static $placeholder = "Placeholder";

	/**
	 * @var string
	 */
	public static $placeholderLbl = "#jform_params_placeholder-lbl";

	/**
	 * @var string
	 */
	public static $placeholderId = "#jform_params_placeholder";

	/**
	 * @var string
	 */
	public static $messageMissingName = "Invalid field:  Name";

	/**
	 * @var string
	 * @since 3.3.28
	 */
	public static $optionTab = "//a[contains(text(),'Options')]";

	/**
	 * @var string
	 * @since 3.3.28
	 */
	public static $addButton = "//button[contains(text(),'Add')]";

	/**
	 * @var string
	 * @since 3.3.28
	 */
	public static $idColumn = "//td[7]";

	/**
	 * @var string
	 * @since 3.3.28
	 */
	public static $repeatFields = "jform_params_field_chzn";

	/**
	 * @param $value
	 * @return string
	 */
	public static function fieldList($value)
	{
		$fieldList = "//a[normalize-space(text()) = \"' . $value . '\"]";

		return $fieldList;
	}

	/**
	 * @param   string $position position row
	 * @return string
	 */
	public static function xpathValueInput($position)
	{
		$xpath = "(//input[@placeholder='Enter value'])[$position]";

		return $xpath;
	}

	/**
	 * @param   string $position position row
	 * @return  string
	 */
	public static function xpathLabelInput($position)
	{
		$xpath = "(//input[@placeholder='Enter label'])[$position]";

		return $xpath;
	}
}
