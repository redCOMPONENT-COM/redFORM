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

		if (isset($params['fieldtype']))
		{
			$I->waitForText(AddAFieldPage::$fieldType, 30, AddAFieldPage::$fieldTypeLbl);
			$I->waitForElementVisible(AddAFieldPage::$fieldTypeID, 30);
			$I->click(AddAFieldPage::$fieldTypeID);
			$I->waitForElementVisible(AddAFieldPage::$fieldTypeInput, 30);
			$I->fillField(AddAFieldPage::$fieldTypeInput, $params['fieldtype']);
			$I->pressKey(AddAFieldPage::$fieldTypeInput, \Facebook\WebDriver\WebDriverKeys::ENTER);
		}

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

		if (isset($params['showon']))
		{
			$I->waitForText(AddAFieldPage::$showOnValueLb, 30);
			$I->fillField(AddAFieldPage::$showOnValueID, $params['showon']);
		}

		if (isset($params['placeholder']))
		{
			$I->waitForText(AddAFieldPage::$placeholder, 30, AddAFieldPage::$placeholderLbl);
			$I->fillField(AddAFieldPage::$placeholderId, $params['placeholder']);
		}

		$I->executeJS('window.scrollTo(0,0);');

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
		$I->wait(0.5);
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
		$I->waitForElementVisible(AddAFieldPage::$checkAll, 30);
		$I->wait(0.5);
		$I->click(AddAFieldPage::$checkAll);
		$I->waitForElementVisible(AddAFieldPage::$copyButtonXpath, 30);
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
		$I->waitForText($name, 30);
		$I->checkAllResults();

		try
		{
			$I->click(AddAFieldPage::$deleteButton);
			$I->acceptPopup();
			$I->wait(2);
			$I->waitForElement(AddAFieldPage::$alertMessage, 60, AddAFieldPage::$alertHead);
			$I->waitForElementVisible(AddAFieldPage::$alertMessage, 60, AddAFieldPage::$alertHead);
		} catch (\Exception $exception)
		{
			$I->wait(1);
			$I->click(AddAFieldPage::$deleteButton);
			$I->acceptPopup();
			$I->wait(2);
			$I->waitForText(AddAFieldPage::$messageNothingData, 30);
		}

		$I->amOnPage(AddAFieldPage::$URL);
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
		$I->waitForText(AddAFieldPage::$field, 30, AddAFieldPage::$headPage);
		$I->waitForJS("return window.jQuery && jQuery.active == 0;", 30);
		$I->waitForElementVisible(AddAFieldPage::$checkAll, 30);
		$I->wait(0.5);
		$I->click(AddAFieldPage::$checkAll);
		$I->wait(0.5);
		$I->click(AddAFieldPage::$deleteButton);
		$I->acceptPopup();
		$I->wait(0.5);

		try
		{
			$I->waitForElementVisible(AddAFieldPage::$alertMessage, 30, AddAFieldPage::$alertHead);
		} catch (\Exception $e)
		{
			$I->waitForText(AddAFieldPage::$messageNothingData, 30);
		}
	}

	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function deleteFieldUsedInForm($name)
	{
		$I = $this;
		$I->amOnPage(AddAFieldPage::$URL);
		$I->searchField($name);
		$I->waitForText($name, 30);
		$I->checkAllResults();
		$I->click(AddAFieldPage::$deleteButton);
		$I->wait(1);
		$I->acceptPopup();
		$I->wait(2);

		try
		{
			$I->waitForElement(AddAFieldPage::$alertMessage, 60);
			$I->searchField($name);
			$I->waitForText($name, 30);
		} catch (\Exception $exception)
		{
			$I->acceptPopup();
			$I->wait(2);
			$I->waitForElement(AddAFieldPage::$alertMessage, 60);
			$I->searchField($name);
			$I->waitForText($name, 30);
		}
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

	/**
	 * @param $nameField
	 * @return mixed
	 * @throws \Exception
	 *  @since 3.3.28
	 */
	public function getFieldID($nameField)
	{
		$i = $this;
		$i->amOnPage(AddAFieldPage::$URL);
		$i->searchField($nameField);
		$i->waitForElementVisible(AddAFieldPage::$idColumn);
		$id = $this->grabTextFrom(AddAFieldPage::$idColumn);

		return $id;
	}

	/**
	 * @param   string $nameField name field
	 * @param   array  $options   option checkbox
	 * @throws \Exception
	 * @since 3.3.28
	 */
	public function addOptionFieldCheckbox($nameField, $options)
	{
		$i = $this;
		$i->amOnPage(AddAFieldPage::$URL);
		$i->searchField($nameField);
		$i->waitForElementVisible(["link" => $nameField], 30);
		$i->click(["link" => $nameField]);
		$i->waitForElementVisible(AddAFieldPage::$optionTab, 30);
		$i->click(AddAFieldPage::$optionTab);
		$i->waitForJS("return window.jQuery && jQuery.active == 0;", 30);

		$length = count($options);

		for ($x = 0; $x < $length; $x++)
		{
			 $y = $x + 1;
			 $option = $options[$x];
			 $i->waitForElementVisible(AddAFieldPage::xpathValueInput($y), 30);
			 $i->fillField(AddAFieldPage::xpathValueInput($y), $option['value']);
			 $i->wait(0.5);
			 $i->waitForElementVisible(AddAFieldPage::xpathLabelInput($y), 30);
			 $i->fillField(AddAFieldPage::xpathLabelInput($y), $option['label']);
			 $i->waitForElementVisible(AddAFieldPage::$addButton, 30);
			 $i->click(AddAFieldPage::$addButton);
		}

		$i->click(AddAFieldPage::$saveCloseButton);
		$i->waitForText(AddAFieldPage::$field, 30, AddAFieldPage::$headPage);
	}

	/**
	 * @param array $params
	 * @param array $function
	 * @throws \Exception
	 */
	public function createFieldRepeat($params = array())
	{
		$i = $this;
		$i->amOnPage(AddAFieldPage::$URL);
		$i->waitForText(AddAFieldPage::$field, 30, AddAFieldPage::$headPage);
		$i->click(AddAFieldPage::$newButton);
		$i->waitForText(AddAFieldPage::$name, 30, AddAFieldPage::$nameLbl);
		$i->fillField(AddAFieldPage::$nameId, $params['name']);

		if (isset($params['fieldtype']))
		{
			$i->waitForText(AddAFieldPage::$fieldType, 30, AddAFieldPage::$fieldTypeLbl);
			$i->waitForElementVisible(AddAFieldPage::$fieldTypeID, 30);
			$i->click(AddAFieldPage::$fieldTypeID);
			$i->waitForElementVisible(AddAFieldPage::$fieldTypeInput, 30);
			$i->fillField(AddAFieldPage::$fieldTypeInput, $params['fieldtype']);
			$i->pressKey(AddAFieldPage::$fieldTypeInput, \Facebook\WebDriver\WebDriverKeys::ENTER);
		}

		if (isset($params['placeholder']))
		{
			$i->waitForText(AddAFieldPage::$placeholder, 30, AddAFieldPage::$placeholderLbl);
			$i->fillField(AddAFieldPage::$placeholderId, $params['placeholder']);
		}

		if (isset($params['targetField']))
		{
			$i->waitForText(AddAFieldPage::$placeholder, 30, AddAFieldPage::$placeholderLbl);
			$i->selectOptionInChosenXpath(AddAFieldPage::$repeatFields, $params['targetField']);
		}

		$i->executeJS('window.scrollTo(0,0);');
		$i->click(AddAFieldPage::$saveCloseButton);
		$i->waitForElement(AddAFieldPage::$alertMessage, 30, AddAFieldPage::$alertHead);
		$i->waitForText(AddAFieldPage::$field, 30, AddAFieldPage::$headPage);
	}
}
