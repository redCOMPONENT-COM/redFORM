<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance;

use Page\Acceptance\AddAFieldPage;

class AddAFieldSteps extends Adminredform
{
	/**
	 * @param array $params
	 * @param array $function
	 * @throws \Exception
	 */
	public function createField($params = array(), $function = array())
	{
		$I = $this;
		$I->amOnPage(AddAFieldPage::$URL);
		$I->waitForText(AddAFieldPage::$field, 30, AddAFieldPage::$headPage);
		$I->click(AddAFieldPage::$newButton);
		$I->waitForText(AddAFieldPage::$name, 30, AddAFieldPage::$nameLbl);
		$I->fillField(AddAFieldPage::$nameId, $params['name']);
		$I->waitForText(AddAFieldPage::$fieldType, 30, AddAFieldPage::$fieldTypeLbl);
		$I->selectOptionInChosen(AddAFieldPage::$fieldType, $params['fieldtype']);

		if (isset($params['field_header']))
		{
			$I->waitForText(AddAFieldPage::$fieldHeader, 30, AddAFieldPage::$fieldHeaderLbl);
			$I->fillField(AddAFieldPage::$fieldHeaderId, $params['field_header']);
		}

		if (isset($params['tooltip']))
		{
			$I->waitForText(AddAFieldPage::$tooltip, 30, AddAFieldPage::$tooltipLbl);
			$I->fillField(AddAFieldPage::$tooltipId, $params['tooltip']);
		}

		if (isset($params['default']))
		{
			$I->waitForText(AddAFieldPage::$defaultValue, 30, AddAFieldPage::$defaultValueLbl);
			$I->fillField(AddAFieldPage::$defaultValueId, $params['default']);
		}
		
		if (isset($params['placeholder']))
		{
			$I->waitForText(AddAFieldPage::$placeholder, 30, AddAFieldPage::$placeholderLbl);
			$I->fillField(AddAFieldPage::$placeholderId, $params['placeholder']);
		}

		switch ($function)
		{
			case 'save':
				$I->click(AddAFieldPage::$saveButton);
				$I->waitForElement(AddAFieldPage::$alertMessage, 30, AddAFieldPage::$alertHead);
				break;

			case 'save&close':
				$I->click(AddAFieldPage::$saveCloseButton);
				$I->waitForElement(AddAFieldPage::$alertMessage, 30, AddAFieldPage::$alertHead);
				$I->waitForText(AddAFieldPage::$field, 30, AddAFieldPage::$headPage);
				break;

			case 'save&new':
				$I->click(AddAFieldPage::$saveNewButton);
				$I->waitForElement(AddAFieldPage::$alertMessage, 30, AddAFieldPage::$alertHead);
				$I->waitForText(AddAFieldPage::$name, 30, AddAFieldPage::$nameLbl);
				break;

			case 'cancel':
				$I->click(AddAFieldPage::$cancelButton);
				$I->waitForText(AddAFieldPage::$field, 30, AddAFieldPage::$headPage);
				break;
		}
	}

	/**
	 * @param array $params
	 * @param array $function
	 * @throws \Exception
	 */
	public function createFieldMissingName($params = array())
	{
		$I = $this;
		$I->amOnPage(AddAFieldPage::$URL);
		$I->waitForText(AddAFieldPage::$field, 30, AddAFieldPage::$headPage);
		$I->click(AddAFieldPage::$newButton);
		$I->waitForText(AddAFieldPage::$fieldType, 30, AddAFieldPage::$fieldTypeLbl);
		$I->selectOptionInChosen(AddAFieldPage::$fieldType, $params['fieldtype']);

		if (isset($params['field_header']))
		{
			$I->waitForText(AddAFieldPage::$fieldHeader, 30, AddAFieldPage::$fieldHeaderLbl);
			$I->fillField(AddAFieldPage::$fieldHeaderId, $params['field_header']);
		}

		if (isset($params['tooltip']))
		{
			$I->waitForText(AddAFieldPage::$tooltip, 30, AddAFieldPage::$tooltipLbl);
			$I->fillField(AddAFieldPage::$tooltipId, $params['tooltip']);
		}

		if (isset($params['default']))
		{
			$I->waitForText(AddAFieldPage::$defaultValue, 30, AddAFieldPage::$defaultValueLbl);
			$I->fillField(AddAFieldPage::$defaultValueId, $params['default']);
		}

		if (isset($params['placeholder']))
		{
			$I->waitForText(AddAFieldPage::$placeholder, 30, AddAFieldPage::$placeholderLbl);
			$I->fillField(AddAFieldPage::$placeholderId, $params['placeholder']);
		}

		$I->click(AddAFieldPage::$saveButton);
		$I->waitForText(AddAFieldPage::$messageMissingName, 30, AddAFieldPage::$alertError);
	}

	/**
	 * @param array $params
	 * @param array $function
	 * @throws \Exception
	 */
	public function editField($name, $params = array(), $function = array())
	{
		$I = $this;
		$I->amOnPage(AddAFieldPage::$URL);
		$I->searchField($name);
		$I->checkAllResults();
		$I->click(AddAFieldPage::$editButton);
		$I->waitForText(AddAFieldPage::$name, 30, AddAFieldPage::$nameLbl);
		$I->fillField(AddAFieldPage::$nameId, $params['name']);
		$I->waitForText(AddAFieldPage::$fieldType, 30, AddAFieldPage::$fieldTypeLbl);
		$I->selectOptionInChosen(AddAFieldPage::$fieldType, $params['fieldtype']);

		if (isset($params['field_header']))
		{
			$I->waitForText(AddAFieldPage::$fieldHeader, 30, AddAFieldPage::$fieldHeaderLbl);
			$I->fillField(AddAFieldPage::$fieldHeaderId, $params['field_header']);
		}

		if (isset($params['tooltip']))
		{
			$I->waitForText(AddAFieldPage::$tooltip, 30, AddAFieldPage::$tooltipLbl);
			$I->fillField(AddAFieldPage::$tooltipId, $params['tooltip']);
		}

		if (isset($params['default']))
		{
			$I->waitForText(AddAFieldPage::$defaultValue, 30, AddAFieldPage::$defaultValueLbl);
			$I->fillField(AddAFieldPage::$defaultValueId, $params['default']);
		}

		switch ($function)
		{
			case 'save':
				$I->click(AddAFieldPage::$saveButton);
				$I->waitForElement(AddAFieldPage::$alertMessage, 30, AddAFieldPage::$alertHead);
				break;

			case 'save&close':
				$I->click(AddAFieldPage::$saveCloseButton);
				$I->waitForElement(AddAFieldPage::$alertMessage, 30, AddAFieldPage::$alertHead);
				$I->waitForText(AddAFieldPage::$field, 30, AddAFieldPage::$headPage);
				break;

			case 'save&new':
				$I->click(AddAFieldPage::$saveNewButton);
				$I->waitForElement(AddAFieldPage::$alertMessage, 30, AddAFieldPage::$alertHead);
				$I->waitForText(AddAFieldPage::$name, 30, AddAFieldPage::$nameLbl);
				break;

			case 'cancel':
				$I->click(AddAFieldPage::$cancelButton);
				$I->waitForText(AddAFieldPage::$field, 30, AddAFieldPage::$headPage);
				break;
		}
	}

	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function copyField($name)
	{
		$I = $this;
		$I->amOnPage(AddAFieldPage::$URL);
		$I->searchField($name);
		$I->checkAllResults();
		$I->click(AddAFieldPage::$copyButton);
		$I->waitForElement(AddAFieldPage::$alertMessage, 30, AddAFieldPage::$alertHead);
		$nameCopy = 'Copy of ' . $name;
		$I->searchField($nameCopy);
		$I->waitForText($nameCopy, 30);
	}

	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function deleteField($name)
	{
		$I = $this;
		$I->amOnPage(AddAFieldPage::$URL);
		$I->searchField($name);
		$I->checkAllResults();
		$I->click(AddAFieldPage::$deleteButton);
		$I->acceptPopup();
		$I->waitForElement(AddAFieldPage::$alertMessage, 30, AddAFieldPage::$alertHead);
		$I->searchField($name);
		$I->dontSee($name);
		$I->waitForElement(AddAFieldPage::$clearButton, 30);
		$I->click(AddAFieldPage::$clearButton);
	}

	/**
	 * @throws \Exception
	 */
	public function deleteAllField()
	{
		$I = $this;
		$I->amOnPage(AddAFieldPage::$URL);
		$I->checkAllResults();
		$I->click(AddAFieldPage::$deleteButton);
		$I->acceptPopup();
		$I->waitForElement(AddAFieldPage::$alertMessage, 30, AddAFieldPage::$alertHead);
	}

	/**
	 * @param $nameField
	 * @throws \Exception
	 */
	public function searchField($nameField)
	{
		$I = $this;
		$I->amOnPage(AddAFieldPage::$URL);
		$I->waitForText(AddAFieldPage::$field, 30, AddAFieldPage::$headPage);
		$I->waitForElement(AddAFieldPage::$searchField, 30);
		$I->fillField(AddAFieldPage::$searchField, $nameField);
		$I->waitForElement(AddAFieldPage::$searchIcon, 30);
		$I->click(AddAFieldPage::$searchIcon);
		$I->waitForText(AddAFieldPage::$field, 30, AddAFieldPage::$headPage);
	}
}