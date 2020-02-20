<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance;

use Page\Acceptance\AddAFormPage as AddAFormPage;
use Page\Acceptance\AddASubmittersPage as AddASubmittersPage;

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

		if (isset($params['displayNotification']))
		{
			$I->waitForElementVisible(AddAFormPage::$notificationTab, 30);
			$I->click(AddAFormPage::$notificationTab);

			switch ($params['displayNotification'])
			{
				case 'yes':
					$I->waitForElementVisible(AddAFormPage::$displayNotificationYes, 30);
					$I->click(AddAFormPage::$displayNotificationYes);
					$I->fillTinyMceEditorById(AddAFormPage::$notificationTextID, $params['notificationMessage']);
					break;

				case 'no':
					$I->waitForElementVisible(AddAFormPage::$displayNotificationNo, 30);
					$I->click(AddAFormPage::$displayNotificationNo);
					break;
			}
		}

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
	 * @param array $params
	 * @throws \Exception
	 */
	public function createFormMissingName($params = array())
	{
		$I = $this;
		$I->amOnPage(AddAFormPage::$url);
		$I->waitForText(AddAFormPage::$form, 30, AddAFormPage::$headPage);
		$I->click(AddAFormPage::$newButton);
		$I->waitForElementVisible(AddAFormPage::$startDateLbl, 30);
		$I->fillField(AddAFormPage::$startDate, $params['startDate']);
		$I->waitForElementVisible(AddAFormPage::$endDateLbl, 30);
		$I->fillField(AddAFormPage::$endDate, $params['endDate']);
		$I->waitForElementVisible(AddAFormPage::$formExpiresLbl, 30);
		$I->selectOptionInRadioField(AddAFormPage::$formExpires, $params['formExpires']);
		$I->click(AddAFormPage::$saveButton);
		$I->waitForText(AddAFormPage::$messageMissingFormName, 30, AddAFormPage::$alertMessage);
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
	public function editFormWithExpires($name, $params = array())
	{
		$I = $this;
		$I->amOnPage(AddAFormPage::$url);
		$I->waitForText(AddAFormPage::$form, 30, AddAFormPage::$headPage);
		$I->searchForm($name);
		$I->checkAllResults();
		$I->click(AddAFormPage::$editButton);
		$I->waitForText(AddAFormPage::$formName, 30, AddAFormPage::$formNameLbl);
		$I->waitForElement(AddAFormPage::$startDateLbl, 30);
		$I->fillField(AddAFormPage::$startDate, $params['startDate']);
		$I->waitForElement(AddAFormPage::$endDateLbl, 30);
		$I->fillField(AddAFormPage::$endDate, $params['endDate']);
		$I->waitForElement(AddAFormPage::$formExpiresLbl, 30);
		$I->selectOptionInRadioField(AddAFormPage::$formExpires, $params['formExpires']);
		$I->click(AddAFormPage::$saveButton);
		$I->waitForText(AddAFormPage::$saveItem, 30, AddAFormPage::$messageSuccess);
	}

	/**
	 * @param       $name
	 * @param array $params
	 * @throws \Exception
	 */
	public function editFormWithConfigNotification($name, $params = array())
	{
		$I = $this;
		$I->amOnPage(AddAFormPage::$url);
		$I->waitForText(AddAFormPage::$form, 30, AddAFormPage::$headPage);
		$I->searchForm($name);
		$I->checkAllResults();
		$I->click(AddAFormPage::$editButton);
		$I->waitForText(AddAFormPage::$formName, 30, AddAFormPage::$formNameLbl);
		$I->click(AddAFormPage::$notification);
		$I->scrollTo(AddAFormPage::$submissionConfirmSubjectLbl);
		$I->waitForElement(AddAFormPage::$submissionConfirmSubject, 30);
		$I->fillField(AddAFormPage::$submissionConfirmSubject, $params['submissionConfirmSubject']);
		$I->scrollTo(AddAFormPage::$toggleEditor);
		$I->click(AddAFormPage::$toggleEditor);
		$I->scrollTo(AddAFormPage::$submissionConfirmBodyLbl);
		$I->waitForElement(AddAFormPage::$submissionConfirmBody, 30);
		$I->fillField(AddAFormPage::$submissionConfirmBody, $params['submissionConfirmBody']);
		$usePage = new AddAFormPage();
		$I->scrollTo($usePage->formEdit($name));
		$I->click(AddAFormPage::$saveCloseButton);
		$I->waitForText(AddAFormPage::$saveItem, 30, AddAFormPage::$messageSuccess);
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
	 * @param       $name
	 * @param array $params
	 * @throws \Exception
	 */
	public function editFormWithConfigConfirmation($name, $params = array(), $option = array())
	{
		$I = $this;
		$I->amOnPage(AddAFormPage::$url);
		$I->waitForText(AddAFormPage::$form, 30, AddAFormPage::$headPage);
		$I->searchForm($name);
		$I->checkAllResults();
		$I->click(AddAFormPage::$editButton);
		$I->waitForText(AddAFormPage::$formName, 30, AddAFormPage::$formNameLbl);
		$I->click(AddAFormPage::$confirmation);
		$I->selectOptionInRadioField(AddAFormPage::$enableConfirmation, $option['enableConfirmation']);
		$I->selectOptionInRadioField(AddAFormPage::$enableConfirmationNotification, $option['enableConfirmationNotification']);
		$I->scrollTo(AddAFormPage::$confirmationNotificationEmailSubjectLbl);
		$I->waitForElement(AddAFormPage::$confirmationNotificationEmailSubject, 30);
		$I->fillField(AddAFormPage::$confirmationNotificationEmailSubject, $params['confirmationNotificationEmailSubject']);
		$I->scrollTo(AddAFormPage::$toggleEditorConfirmation);
		$I->click(AddAFormPage::$toggleEditorConfirmation);
		$I->scrollTo(AddAFormPage::$confirmationNotificationEmailBodyLbl);
		$I->waitForElement(AddAFormPage::$confirmationNotificationEmailBody, 30);
		$I->fillField(AddAFormPage::$confirmationNotificationEmailBody, $params['confirmationNotificationEmailBody']);
		$usePage = new AddAFormPage();
		$I->scrollTo($usePage->formEdit($name));
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
	public function deleteFormHasSubmitters($nameForm)
	{
		$I = $this;
		$I->amOnPage(AddAFormPage::$url);
		$I->waitForText(AddAFormPage::$form, 30, AddAFormPage::$headPage);
		$I->searchForm($nameForm);
		$I->checkAllResults();
		$I->click(AddAFormPage::$deleteButton);
		$I->acceptPopup();
		$I->wait(1);
		$I->waitForElement(AddAFormPage::$alertMessage, 60);
		$I = $this;
		$I->amOnPage(AddASubmittersPage::$URL);
		$I->waitForText(AddASubmittersPage::$submitters, 30, AddASubmittersPage::$headPage);
		$I->waitForElementVisible(AddASubmittersPage::$selectFormId, 30);
		$I->waitForElement(AddASubmittersPage::$selectFormId, 30);
		$I->selectOptionInChosenXpath(AddASubmittersPage::$selectForm, $nameForm);
		$I->waitForText($nameForm, 30);
		$I->checkAllResults();
		$I->wait(0.5);
		$I->click(AddASubmittersPage::$deleteButton);
		$I->acceptPopup();
		$I->wait(2);
		$I->waitForText(AddASubmittersPage::$messageNothingData, 60);
		$I->amOnPage(AddAFormPage::$url);
		$I->waitForText(AddAFormPage::$form, 30, AddAFormPage::$headPage);
		$I->searchForm($nameForm);
		$I->checkAllResults();
		$I->click(AddAFormPage::$deleteButton);
		$I->acceptPopup();
		$I->wait(1);

		try
		{
			$I->waitForText(AddAFormPage::$deleteSuccess, 60, AddAFormPage::$alertMessage);
		}catch (\Exception $e)
		{
			$I->waitForText(AddAFormPage::$messageNothingData, 30);
		}
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

		try
		{
			$I->click(AddAFormPage::$deleteButton);
			$I->acceptPopup();
			$I->wait(2);
			$I->waitForElement(AddAFormPage::$alertMessage, 60);
		} catch (\Exception $exception)
		{
			$I->waitForText(AddAFormPage::$messageNothingData, 30);
		}
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

	/**
	 * @param $nameField
	 * @throws \Exception
	 * @since 3.3.28
	 */
	public function searchFormFields($nameField)
	{
		$i = $this;
		$i->waitForElementVisible(AddAFormPage::$searchField, 30);
		$i->fillField(AddAFormPage::$searchField, $nameField);
		$i->waitForElement(AddAFormPage::$searchIcon, 30);
		$i->click(AddAFormPage::$searchIcon);
		$i->waitForText("Form fields", 30, "//h2");
	}

	/**
	 * @param array $params
	 * @throws \Exception
	 * @since 3.3.28
	 */
	public function changeStatusFormField($params = array())
	{
		$i = $this;
		$i->amOnPage(AddAFormPage::$url);
		$i->waitForText(AddAFormPage::$form, 30, AddAFormPage::$headPage);
		$i->searchForm($params['name']);
		$i->checkAllResults();
		$i->click(AddAFormPage::$editButton);
		$i->waitForElementVisible(AddAFormPage::$fields, 30);
		$i->click(AddAFormPage::$fields);

		$i->searchFormFields($params['fields_1']);
		$i->checkAllResults();
		$i->waitForText($params['status_1'], 30);
		$i->click($params['status_1']);

		$i->searchFormFields($params['fields_2']);
		$i->checkAllResults();
		$i->waitForText($params['status_2'], 30);
		$i->click($params['status_2']);

		$i->click(AddAFormPage::$saveCloseButton);
	}

	/**
	 * @param array $params
	 * @throws \Exception
	 * @since 3.3.28
	 */
	public function selectStatusFormField($params = array())
	{
		$i = $this;
		$i->amOnPage(AddAFormPage::$url);
		$i->waitForText(AddAFormPage::$form, 30, AddAFormPage::$headPage);
		$i->searchForm($params['name']);
		$i->checkAllResults();
		$i->click(AddAFormPage::$editButton);
		$i->waitForElementVisible(AddAFormPage::$fields, 30);
		$i->click(AddAFormPage::$fields);

		$i->waitForElementVisible(AddAFormPage::$clearButton, 30);
		$i->click(AddAFormPage::$clearButton);

		if ($params['status_1'] == "Publish")
		{
			$i->waitForElementVisible(AddAFormPage::$searchToolsButton, 30);
			$i->click(AddAFormPage::$searchToolsButton);
			$i->waitForElementVisible(AddAFormPage::$statusSelectInput, 30);
			$i->selectOptionInChosenById(AddAFormPage::$statusSelectId, "Published");
			$i->waitForText($params['fields_1'], 10);
		}
		else
		{
			$i->waitForElementVisible(AddAFormPage::$searchToolsButton, 30);
			$i->click(AddAFormPage::$searchToolsButton);
			$i->waitForElementVisible(AddAFormPage::$statusSelectInput, 30);
			$i->selectOptionInChosenById(AddAFormPage::$statusSelectId, "Unpublished");
			$i->waitForText($params['fields_1'], 10);
		}

		if ($params['status_2'] == "Publish")
		{
			$i->waitForElementVisible(AddAFormPage::$searchToolsButton, 30);
			$i->click(AddAFormPage::$searchToolsButton);
			$i->waitForElementVisible(AddAFormPage::$statusSelectInput, 30);
			$i->selectOptionInChosenById(AddAFormPage::$statusSelectId, "Published");
			$i->waitForText($params['fields_2'], 10);
		}
		else
		{
			$i->waitForElementVisible(AddAFormPage::$searchToolsButton, 30);
			$i->click(AddAFormPage::$searchToolsButton);
			$i->waitForElementVisible(AddAFormPage::$statusSelectInput, 30);
			$i->selectOptionInChosenById(AddAFormPage::$statusSelectId, "Unpublished");
			$i->waitForText($params['fields_2'], 10);
		}

		$i->click(AddAFormPage::$saveCloseButton);
	}
}
