<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance;

use Page\Acceptance\AddASubmittersPage as AddASubmittersPage;

class AddASubmittersSteps extends Adminredform
{
	/**
	 * @param $formName
	 * @throws \Exception
	 */
	public function checkCreateSubmitters($formName, $name, $email)
	{
		$I = $this;
		$I->amOnPage(AddASubmittersPage::$URL);
		$I->waitForText(AddASubmittersPage::$submitters, 30, AddASubmittersPage::$headPage);
		$I->waitForElement(AddASubmittersPage::$selectFormId, 30);
		$I->selectOptionInChosenXpath(AddASubmittersPage::$selectForm, $formName);
		$I->waitForText($name, 30);
		$I->waitForText($email, 30);
	}

	/**
	 * @param $formName
	 * @throws \Exception
	 */
	public function deleteAllSubmitters($formName)
	{
		$I = $this;
		$I->amOnPage(AddASubmittersPage::$URL);
		$I->waitForText(AddASubmittersPage::$submitters, 30, AddASubmittersPage::$headPage);
		$I->waitForElement(AddASubmittersPage::$selectFormId, 30);
		$I->selectOptionInChosenXpath(AddASubmittersPage::$selectForm, $formName);
		$I->checkAllResults();
		$I->click(AddASubmittersPage::$deleteButton);
		$I->acceptPopup();
		$I->waitForElement(AddASubmittersPage::$alertMessage, 30, AddASubmittersPage::$alertHead);
	}

}