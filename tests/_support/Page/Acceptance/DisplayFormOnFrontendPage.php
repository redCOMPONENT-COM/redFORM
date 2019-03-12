<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2019 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance;

class DisplayFormOnFrontendPage extends RedFormAdminPage
{
	/**
	 * @var string
	 */
	public static $frontendURL = "index.php";
	/**
	 * @var string
	 */
	public static $adminArticlesURL = "administrator/index.php?option=com_content&view=articles";
	/**
	 * @var string
	 */
	public static $menuItemURL = '/administrator/index.php?option=com_menus&view=menus';
	/**
	 * @var string
	 */
	public static $h1 =  array('css' => 'h1');
	/**
	 * @var string
	 */
	public static $menuTitle   = 'Menus';
	/**
	 * @var string
	 */
	public static $menuItemsTitle   = 'Menus: Items';
	/**
	 * @var string
	 */
	public static $menuNewItemTitle   = 'Menus: New Item';
	/**
	 * Menu item title
	 * @var string
	 */
	public static $menItemTitle = "#jform_title";
	/**
	 * @var string
	 */
	public static $titleLbl = "#jform_title-lbl";
	/**
	 * @var string
	 */
	public static $title = "//input[@id='jform_title']";
	/**
	 * @var string
	 */
	public static $tagForm = "//table[@id='formList']/tbody/tr/td[8]";
	/**
	 * @var string
	 */
	public static $toggleEditor = "//a[@title='Toggle editor']";
	/**
	 * @var string
	 */
	public static $contentField = "//textarea[@name='jform[articletext]']";
	/**
	 * @var string
	 */
	public static $menuItemType = "Menu Item Type";
	/**
	 * @var string
	 */
	public static $menuItemTypeLbl = "#jform_type-lbl";
	/**
	 * @var string
	 */
	public static $selectMenuItemType = "Select";
	/**
	 * @var string
	 */
	public static $selectArticleLbl = "#jform_request_id_id-lbl";
	/**
	 * @var string
	 */
	public static $createArticle = "//button[@id='jform_request_id_new']";
	/**
	 * @var string
	 */
	public static $selectArticle = "//button[@id='jform_request_id_select']";
	/**
	 * @var string
	 */
	public static $selectChangeArticle = "Select or Change article";
	/**
	 * @var string
	 */
	public static $searchArticleId  = "#filter_search";
	/**
	 * @var string
	 */
	public static $saveCloseMenuItemButton = "//button[@class='btn btn-small button-save']";
	/**
	 * @var string
	 */
	public static $nameInput = "//input[@placeholder='Please enter your name']";
	/**
	 * @var string
	 */
	public static $emailInput = "//input[@placeholder='Please enter your email']";
	/**
	 * @var string
	 */
	public static $telephoneInput = "//input[@placeholder='Please enter your telephone']";
	/**
	 * @var string
	 */
	public static $noteTextarea = "//textarea[@placeholder='Please enter your note']";
	/**
	 * @var string
	 */
	public static $regularSubmit = "//input[@id='regularsubmit']";
	/**
	 * @param $menuItem
	 * @return string
	 */
	public static function returnMenuItem($menuItem)
	{
		$path = "//a[contains(text()[normalize-space()], '$menuItem')]";
		return $path;
	}
	/**
	 * @param $value
	 * @return string
	 */
	public static function xPathMenu($value)
	{
		$xpath = "//a[contains(text(), '$value')]";
		return $xpath;
	}
}