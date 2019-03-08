<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance;

use Page\Acceptance\AddAFormPage as AddAFormPage;

class AddAFormSteps extends Adminredform
{
	/**
	 * @param array $params
	 * @param array $function
	 * @throws \Exception
	 */
	public function createForm($params = array(), $function = array())
	{
		$I = $this;
		$I->amOnPage(AddAFormPage::$url);
		$I->waitForText(AddAFormPage::$form, 30, AddAFormPage::$headPage);
		$I->click(AddAFormPage::$newButton);
		$I->waitForText(AddAFormPage::$formName, 30, AddAFormPage::$formNameLbl);
		$I->fillField(AddAFormPage::$formNameId, $params['name']);
		switch ($function)
		{
			case 'save':
				$I->click(AddAFormPage::$saveButton);
				$I->waitForText(AddAFormPage::$saveItem, 30, AddAFormPage::$messageSuccess);
				$I->click(AddAFormPage::$fields);
				$I->click(AddAFormPage::$newButton);
				$I->waitForText(AddAFormPage::$formField, 30, AddAFormPage::$headPage);
				$I->selectOptionInChosenByIdUsingJs(AddAFormPage::$fieldId, $params['fields_1']);
				$I->selectOptionInChosenByIdUsingJs(AddAFormPage::$sectionId, $params['section_1']);
				$I->selectOptionInRadioField(AddAFormPage::$required, $params['required']);
				$I->click(AddAFormPage::$saveCloseButton);
				$I->waitForText(AddAFormPage::$saveItem, 30, AddAFormPage::$messageSuccess);
				$I->click(AddAFormPage::$newButton);
				$I->waitForText(AddAFormPage::$formField, 30, AddAFormPage::$headPage);
				$I->selectOptionInChosenByIdUsingJs(AddAFormPage::$fieldId, $params['fields_2']);
				$I->selectOptionInChosenByIdUsingJs(AddAFormPage::$sectionId, $params['section_2']);
				$I->selectOptionInRadioField(AddAFormPage::$required, $params['required']);
				$I->click(AddAFormPage::$saveCloseButton);
				$I->waitForText(AddAFormPage::$saveItem, 30, AddAFormPage::$messageSuccess);
				$I->click(AddAFormPage::$saveButton);
				$I->waitForText(AddAFormPage::$saveItem, 30, AddAFormPage::$messageSuccess);
				break;

			case 'save&close':
				$I->click(AddAFormPage::$saveCloseButton);
				$I->waitForText(AddAFormPage::$saveItem, 30, AddAFormPage::$messageSuccess);
				$I->searchForm($params['name']);
				$I->checkAllResults();
				$I->click(AddAFormPage::$editButton);
				$I->click(AddAFormPage::$fields);
				$I->click(AddAFormPage::$newButton);
				$I->waitForText(AddAFormPage::$formField, 30, AddAFormPage::$headPage);
				$I->selectOptionInChosenByIdUsingJs(AddAFormPage::$fieldId, $params['fields_1']);
				$I->selectOptionInChosenByIdUsingJs(AddAFormPage::$sectionId, $params['section_1']);
				$I->selectOptionInRadioField(AddAFormPage::$required, $params['required']);
				$I->click(AddAFormPage::$saveCloseButton);
				$I->waitForText(AddAFormPage::$saveItem, 30, AddAFormPage::$messageSuccess);
				$I->click(AddAFormPage::$newButton);
				$I->waitForText(AddAFormPage::$formField, 30, AddAFormPage::$headPage);
				$I->selectOptionInChosenByIdUsingJs(AddAFormPage::$fieldId, $params['fields_2']);
				$I->selectOptionInChosenByIdUsingJs(AddAFormPage::$sectionId, $params['section_2']);
				$I->selectOptionInRadioField(AddAFormPage::$required, $params['required']);
				$I->click(AddAFormPage::$saveCloseButton);
				$I->waitForText(AddAFormPage::$saveItem, 30, AddAFormPage::$messageSuccess);
				$I->click(AddAFormPage::$saveButton);
				$I->waitForText(AddAFormPage::$saveItem, 30, AddAFormPage::$messageSuccess);
				break;
		}
	}

	/**
	 * @param       $name
	 * @param array $params
	 * @param array $function
	 * @throws \Exception
	 */
	public function editForm($name, $nameEdit, $function = array())
	{
		$I = $this;
		$I->amOnPage(AddAFormPage::$url);
		$I->waitForText(AddAFormPage::$form, 30, AddAFormPage::$headPage);
		$I->searchForm($name);
		$I->checkAllResults();
		$I->click(AddAFormPage::$editButton);
		$I->waitForText(AddAFormPage::$formName, 30, AddAFormPage::$formNameLbl);
		$I->fillField(AddAFormPage::$formNameId, $nameEdit);
		switch ($function)
		{
			case 'save':
				$I->click(AddAFormPage::$saveButton);
				$I->waitForText(AddAFormPage::$saveItem, 30, AddAFormPage::$messageSuccess);
				break;

			case 'save&close':
				$I->click(AddAFormPage::$saveCloseButton);
				$I->waitForText(AddAFormPage::$saveItem, 30, AddAFormPage::$messageSuccess);
				$I->waitForText(AddAFormPage::$form, 30, AddAFormPage::$headPage);
				break;
		}

	}

	/**
	 * @param       $name
	 * @param array $params
	 * @throws \Exception
	 */
	public function editAndAddFieldForForm($name, $params = array())
	{
		$I = $this;
		$I->amOnPage(AddAFormPage::$url);
		$I->waitForText(AddAFormPage::$form, 30, AddAFormPage::$headPage);
		$I->searchForm($name);
		$I->checkAllResults();
		$I->click(AddAFormPage::$editButton);
		$I->waitForText(AddAFormPage::$formName, 30, AddAFormPage::$formNameLbl);
		$I->selectOptionInRadioField(AddAFormPage::$formExpires, $params['formExpires']);
		$I->click(AddAFormPage::$saveButton);
		$I->waitForText(AddAFormPage::$saveItem, 30, AddAFormPage::$messageSuccess);
		$I->click(AddAFormPage::$fields);
		$I->click(AddAFormPage::$newButton);
		$I->waitForText(AddAFormPage::$formField, 30, AddAFormPage::$headPage);
		$I->selectOptionInChosenByIdUsingJs(AddAFormPage::$fieldId, $params['fields']);
		$I->selectOptionInChosenByIdUsingJs(AddAFormPage::$sectionId, $params['section']);
		$I->selectOptionInRadioField(AddAFormPage::$required, $params['required']);
		$I->click(AddAFormPage::$saveCloseButton);
		$I->waitForText(AddAFormPage::$saveItem, 30, AddAFormPage::$messageSuccess);
		$I->click(AddAFormPage::$saveCloseButton);
		$I->waitForText(AddAFormPage::$saveItem, 30, AddAFormPage::$messageSuccess);
	}

	/**
	 * @param $nameForm
	 * @throws \Exception
	 */
	public function publishForm($nameForm)
	{
		$I = $this;
		$I->amOnPage(AddAFormPage::$url);
		$I->waitForText(AddAFormPage::$form, 30, AddAFormPage::$headPage);
		$I->searchForm($nameForm);
		$I->checkAllResults();
		$I->click(AddAFormPage::$publishButton);
		$I->waitForElement(AddAFormPage::$alertMessage, 30, AddAFormPage::$alertHead);
	}

	/**
	 * @param $nameForm
	 * @throws \Exception
	 */
	public function unpublishForm($nameForm)
	{
		$I = $this;
		$I->amOnPage(AddAFormPage::$url);
		$I->waitForText(AddAFormPage::$form, 30, AddAFormPage::$headPage);
		$I->searchForm($nameForm);
		$I->checkAllResults();
		$I->click(AddAFormPage::$unpublishButton);
		$I->waitForElement(AddAFormPage::$alertMessage, 30, AddAFormPage::$alertHead);
	}

	/**
	 * @param $nameForm
	 * @throws \Exception
	 */
	public function deleteForm($nameForm)
	{
		$I = $this;
		$I->amOnPage(AddAFormPage::$url);
		$I->waitForText(AddAFormPage::$form, 30, AddAFormPage::$headPage);
		$I->searchForm($nameForm);
		$I->checkAllResults();
		$I->click(AddAFormPage::$deleteButton);
		$I->acceptPopup();
		$I->waitForElement(AddAFormPage::$alertMessage, 30, AddAFormPage::$alertHead);
	}

	/**
	 * @param $nameForm
	 * @throws \Exception
	 */
	public function searchForm($nameForm)
	{
		$I = $this;
		$I->amOnPage(AddAFormPage::$url);
		$I->waitForText(AddAFormPage::$form, 30, AddAFormPage::$headPage);
		$I->waitForElement(AddAFormPage::$searchForm, 30);
		$I->fillField(AddAFormPage::$searchForm, $nameForm);
		$I->waitForElement(AddAFormPage::$searchIcon, 30);
		$I->click(AddAFormPage::$searchIcon);
		$I->waitForText(AddAFormPage::$form, 30, AddAFormPage::$headPage);
	}
}