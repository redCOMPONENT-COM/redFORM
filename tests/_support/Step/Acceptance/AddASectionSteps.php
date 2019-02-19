<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance;

use Page\Acceptance\AddASectionPage as AddASectionPage;

class AddASectionSteps extends Adminredform
{
	/**
	 * @param array $params
	 * @param array $function
	 * @throws \Exception
	 */
	public function createSection($params = array(), $function = array())
	{
		$I = $this;
		$I->amOnPage(AddASectionPage::$URL);
		$I->waitForText(AddASectionPage::$section, 30, AddASectionPage::$headPage);
		$I->click(AddASectionPage::$newButton);
		$I->waitForText(AddASectionPage::$name, 30, AddASectionPage::$nameLbl);
		$I->fillField(AddASectionPage::$nameId, $params['name']);

		if (!empty($params['class']))
		{
			$I->waitForText(AddASectionPage::$class, 30, AddASectionPage::$classLbl);
			$I->fillField(AddASectionPage::$classId, $params['class']);
		}

		switch ($function)
		{
			case 'save':
				$I->click(AddASectionPage::$saveButton);
				$I->waitForText(AddASectionPage::$saveItem, 30, AddASectionPage::$messageSuccess);
				$I->see(AddASectionPage::$saveItem);
				break;

			case 'save&close':
				$I->click(AddASectionPage::$saveCloseButton);
				$I->waitForText(AddASectionPage::$saveItem, 30, AddASectionPage::$messageSuccess);
				$I->waitForText(AddASectionPage::$section, 30, AddASectionPage::$headPage);
				break;

			case 'cancel':
				$I->click(AddASectionPage::$cancelButton);
				$I->waitForText(AddASectionPage::$section, 30, AddASectionPage::$headPage);
				break;
		}
	}

	/**
	 * @param       $name
	 * @param array $params
	 * @param array $function
	 * @throws \Exception
	 */
	public function editSection($name, $params = array(), $function = array())
	{
		$I = $this;
		$I->amOnPage(AddASectionPage::$URL);
		$I->searchSection($name);
		$I->wait(0.5);
		$I->checkAllResults();
		$I->click(AddASectionPage::$editButton);
		$I->waitForText(AddASectionPage::$name, 30, AddASectionPage::$nameLbl);
		$I->fillField(AddASectionPage::$nameId, $params['name']);

		if (!empty($params['class']))
		{
			$I->waitForText(AddASectionPage::$class, 30, AddASectionPage::$classLbl);
			$I->fillField(AddASectionPage::$classId, $params['class']);
		}

		switch ($function)
		{
			case 'save':
				$I->click(AddASectionPage::$saveButton);
				$I->waitForText(AddASectionPage::$saveItem, 30, AddASectionPage::$messageSuccess);
				break;

			case 'save&close':
				$I->click(AddASectionPage::$saveCloseButton);
				$I->waitForText(AddASectionPage::$saveItem, 30, AddASectionPage::$messageSuccess);
				$I->waitForText(AddASectionPage::$section, 30, AddASectionPage::$headPage);
				break;

			case 'cancel':
				$I->click(AddASectionPage::$cancelButton);
				$I->waitForText(AddASectionPage::$section, 30, AddASectionPage::$headPage);
				break;
		}
	}

	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function deleteSection($name)
	{
		$I = $this;
		$I->amOnPage(AddASectionPage::$URL);
		$I->searchSection($name);
		$I->wait(0.5);
		$I->checkAllResults();
		$I->click(AddASectionPage::$deleteButton);
		$I->waitForElement(AddASectionPage::$alertMessage, 30, AddASectionPage::$alertHead);
		$I->searchSection($name);
		$I->dontSee($name);
	}

	/**
	 * @param $nameSection
	 * @throws \Exception
	 */
	public function searchSection($nameSection)
	{
		$I = $this;
		$I->amOnPage(AddASectionPage::$URL);
		$I->waitForText(AddASectionPage::$section, 30, AddASectionPage::$headPage);
		$I->waitForElement(AddASectionPage::$searchSection, 30);
		$I->fillField(AddASectionPage::$searchSection, $nameSection);
		$I->waitForElement(AddASectionPage::$searchIcon, 30);
		$I->click(AddASectionPage::$searchIcon);
		$I->waitForText(AddASectionPage::$section, 30, AddASectionPage::$headPage);
	}


}