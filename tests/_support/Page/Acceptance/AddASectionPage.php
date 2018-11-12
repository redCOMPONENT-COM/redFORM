<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance;

class AddASectionPage extends RedFormAdminPage
{
	/**
	 * @var string
	 */
	public static $URL = "administrator/index.php?option=com_redform&view=sections";
	/**
	 * @var string
	 */
	public static $section = "Sections";
	/**
	 * @var string
	 */
	public static $name = "Name";
	/**
	 * @var string
	 */
	public static $nameLbl = "#jform_name-lbl";
	/**
	 * @var string
	 */
	public static $nameId = "#jform_name";
	/**
	 * @var string
	 */
	public static $class = "Css class";
	/**
	 * @var string
	 */
	public static $classLbl = "#jform_class-lbl";
	/**
	 * @var string
	 */
	public static $classId = "#jform_class";
	/**
	 * @var string
	 */
	public static $description = "Description";
	/**
	 * @var string
	 */
	public static $descriptionLbl = "#jform_description-lbl";
	/**
	 * @var string
	 */
	public static $descriptionId = "//div[@id=\"mceu_78\"]";
	/**
	 * @param $value
	 * @return string
	 */
	public static function sectionItem($value)
	{
		$sectionItem = "//tbody/tr/td/a[normalize-space(text()) = \"' . $value . '\"]";
		return $sectionItem;
	}
}