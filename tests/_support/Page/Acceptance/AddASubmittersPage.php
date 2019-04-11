<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2019 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Page\Acceptance;


class AddASubmittersPage extends RedFormAdminPage
{
	/**
	 * @var string
	 */
	public static $URL = "administrator/index.php?option=com_redform&view=submitters";

	/**
	 * @var string
	 */
	public static $submitters = "Submitters";

	/**
	 * @var string
	 */
	public static $selectForm = "filter_form_id_chzn";

	/**
	 * @var string
	 */
	public static $selectFormId = "//div[@id='filter_form_id_chzn']/a";


}