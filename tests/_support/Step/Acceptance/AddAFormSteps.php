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
	 * Create a form
	 *
	 * @param   array  $params  section fields
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function createForm($params)
	{
		$I = $this;
		$I->amOnPage(AddAFormPage::$url);
		$I->waitForText(AddAFormPage::$form, 30, AddAFormPage::$headPage);
		$I->click(AddAFormPage::$newButton);
		$I->waitForText(AddAFormPage::$formName, 30, AddAFormPage::$formNameLbl);
		$I->fillField(AddAFormPage::$formNameId, $params['name']);
		$I->click(AddAFormPage::$saveButton);
		$I->waitForText(AddAFormPage::$saveItem, 30, AddAFormPage::$messageSuccess);
		$I->click(AddAFormPage::$fields);
		$I->click(AddAFormPage::$newButton);
		$I->waitForText(AddAFormPage::$formField, 30, AddAFormPage::$headPage);
		$I->selectOptionInChosenByIdUsingJs(AddAFormPage::$fieldId, $params['fields_1']);
		$I->selectOptionInChosenByIdUsingJs(AddAFormPage::$sectionId, $params['section_1']);
		$I->click(AddAFormPage::$saveCloseButton);
		$I->waitForText(AddAFormPage::$saveItem, 30, AddAFormPage::$messageSuccess);
		$I->click(AddAFormPage::$newButton);
		$I->waitForText(AddAFormPage::$formField, 30, AddAFormPage::$headPage);
		$I->selectOptionInChosenByIdUsingJs(AddAFormPage::$fieldId, $params['fields_2']);
		$I->selectOptionInChosenByIdUsingJs(AddAFormPage::$sectionId, $params['section_2']);
		$I->click(AddAFormPage::$saveCloseButton);
		$I->waitForText(AddAFormPage::$saveItem, 30, AddAFormPage::$messageSuccess);
		$I->click(AddAFormPage::$saveButton);
		$I->waitForText(AddAFormPage::$saveItem, 30, AddAFormPage::$messageSuccess);
	}
}