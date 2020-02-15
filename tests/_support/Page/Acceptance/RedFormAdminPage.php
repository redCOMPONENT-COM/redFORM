<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance;

class RedFormAdminPage
{
	//general button

	/**
	 * @var string
	 */
	public static $urlSystem = "administrator/index.php?option=com_config";

	/**
	 * @var string
	 */
	public static $server = "//a[contains(text(),'Server')]";

	/**
	 * @var string
	 */
	public static $mailSetting = "//legend[contains(text(),'Mail Settings')]";

	/**
	 * @var string
	 */
	public static $sendMail = "Send Mail";

	/**
	 * @var string
	 */
	public static $newButton = "New";

	/**
	 * @var string
	 */
	public static $copyButton = "Copy";

	/**
	 * @var string
	 * @since 3.3.28
	 */
	public static $checkAll = "//input[@name='checkall-toggle']";

	/**
	 * @var string
	 * @since 3.3.28
	 */
	public static $copyButtonXpath = "//i[@class='icon-copy']";

	/**
	 * @var string
	 */
	public static $editButton = "Edit";

	/**
	 * @var string
	 */
	public static $publishButton = "Publish";

	/**
	 * @var string
	 */
	public static $unpublishButton = "Unpublish";

	/**
	 * @var string
	 */
	public static $deleteButton = "Delete";

	/**
	 * @var string
	 */
	public static $saveButton = "Save";

	/**
	 * @var string
	 */
	public static $saveCloseButton = "Save & Close";

	/**
	 * @var string
	 */
	public static $saveNewButton = "Save & New";

	/**
	 * @var string
	 */
	public static $cancelButton = "Cancel";

	/**
	 * @var string
	 */
	public static $autodeleteSubmittersButton = "Autodelete submitters";

	/**
	 * @var string
	 */
	public static $clearButton = "//button[@data-original-title=\"Clear\"]";

	/**
	 * @var string
	 */
	public static $searchToolsButton = "//button[@data-original-title=\"Filter the list items\"]";

	//general system
	/**
	 * @var string
	 */
	public static $messageSuccess = '.alert-success';

	/**
	 * @var string
	 */
	public static $alertMessage = "//div[@class='alert-message']";

	/**
	 * @var string
	 */
	public static $alertHead = "//h4[@class='alert-heading']";

	/**
	 * @var string
	 */
	public static $alertError = ".alert-error";

	/**
	 * @var string
	 */
	public static $headPage = "//h1";

	//general message
	/**
	 * @var string
	 */
	public static $saveItem = 'Item saved.';

	/**
	 * @var string
	 */
	public static $messageWarning = 'Warning';

	/**
	 * @var string
	 */
	public static $messageErrorSave = 'Error';

	/**
	 * @var string
	 */
	public static $publishOneSuccess = "1 items published";

	/**
	 * @var string
	 */
	public static $unpublishOneSuccess = "1 items unpublished";

	/**
	 * @var string
	 */
	public static $deleteSuccess = "1 items deleted";

	//general located
	/**
	 * @var string
	 */
	public static $searchForm = "#filter_search_forms";

	/**
	 * @var string
	 */
	public static $searchField = "#filter_search_fields";

	/**
	 * @var string
	 */
	public static $searchSection = "#filter_search_sections";

	/**
	 * @var string
	 */
	public static $searchIcon = "//button[@data-original-title=\"Search\"]";

	/**
	 * @var string
	 * @since 3.3.28
	 */
	public static $messageNothingData = "Nothing to display";

	/**
	 * @param $id
	 * @return string
	 */
	public function selectXpath($id)
	{
		$xpath = "//div[@id='$id']/a";
		return $xpath;
	}

	/**
	 * @param $id
	 * @param $value
	 * @return string
	 */
	public function selectXpathValue($id, $value)
	{
		$xpath = "//div[@id='$id']/div/ul/li[contains(normalize-space(),'$value')]";
		return $xpath;
	}

}
