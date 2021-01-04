<?php
/**
 * @package     redFORM
 * @subpackage  Steps DisplayFormOnFrontend
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance;

use Exception;
use Codeception\Scenario;
use Page\Acceptance\DisplayFormOnFrontendPage;
use Page\Acceptance\AddAFormPage;

class DisplayFormOnFrontendSteps  extends Adminredform
{
	/**
	 * @param                       $formName
	 * @param                       $articlesTitle
	 * @param Scenario $scenario
	 * @throws Exception
	 */
	public function createNewArticle($formName, $articlesTitle, Scenario $scenario)
	{
		$I = $this;
		$I->amOnPage(AddAFormPage::$url);
		$I->waitForText(AddAFormPage::$form, 30, AddAFormPage::$headPage);
		$I = new AddAFormSteps($scenario);
		$I->wantTo('Grab the Tag of form');
		$I->searchForm($formName);
		$I->waitForElement(DisplayFormOnFrontendPage::$tagForm, 30);
		$tag = $I->grabTextFrom(DisplayFormOnFrontendPage::$tagForm);
		$I->waitForText($tag, 30);
		$I->amOnPage(DisplayFormOnFrontendPage::$adminArticlesURL);
		$I->wantTo('Create new article use form');
		$I->click(DisplayFormOnFrontendPage::$newButton);
		$I->waitForElement(DisplayFormOnFrontendPage::$titleLbl, 30);
		$I->fillField(DisplayFormOnFrontendPage::$title, $articlesTitle);
		$I->scrollTo(DisplayFormOnFrontendPage::$toggleEditor);
		$I->click(DisplayFormOnFrontendPage::$toggleEditor);
		$I->waitForElement(DisplayFormOnFrontendPage::$contentField, 30);
		$I->fillField(DisplayFormOnFrontendPage::$contentField, $tag);
		$I->click(DisplayFormOnFrontendPage::$saveCloseButton);
		$I->waitForElement(DisplayFormOnFrontendPage::$alertMessage, 30, DisplayFormOnFrontendPage::$alertHead);
	}

	/**
	 * @param $articlesTitle
	 * @param $articles
	 * @param $menuTitle
	 * @param $menuItemType
	 * @param $menu
	 * @throws Exception
	 */
	public function createNewMenuItem($articlesTitle, $articles, $menuTitle, $menuItemType, $menu)
	{
		$I = $this;
		$I->amOnPage(DisplayFormOnFrontendPage::$menuItemURL);
		$I->waitForText(DisplayFormOnFrontendPage::$menuTitle, 30, DisplayFormOnFrontendPage::$h1);
		$I->checkForPhpNoticesOrWarnings();

		$I->click(array('link' => $menu));
		$I->waitForText(DisplayFormOnFrontendPage::$menuItemsTitle, 30, DisplayFormOnFrontendPage::$h1);
		$I->checkForPhpNoticesOrWarnings();

		$I->click(DisplayFormOnFrontendPage::$newButton);
		$I->waitForElement(DisplayFormOnFrontendPage::$titleLbl, 30);
		$I->fillField(DisplayFormOnFrontendPage::$title, $menuTitle);
		$I->waitForElement(DisplayFormOnFrontendPage::$menuItemTypeLbl, 30);
		$I->click(DisplayFormOnFrontendPage::$selectMenuItemType);
		$I->switchToIFrame(DisplayFormOnFrontendPage::$menuItemType);
		$I->wait(2);
		$I->waitForElementVisible(['link' => $menuItemType], 60);
		$I->wait(1);
		$I->click(['link' => $menuItemType]);
		$usePage = new DisplayFormOnFrontendPage();
		$I->waitForElementVisible($usePage->returnMenuItem($articles), 60);
		$I->wait(0.5);
		$I->click($usePage->returnMenuItem($articles));
		$I->waitForElement(DisplayFormOnFrontendPage::$selectArticleLbl, 30);
		$I->waitForElement(DisplayFormOnFrontendPage::$selectArticle, 30);
		$I->click(DisplayFormOnFrontendPage::$selectArticle);
		$I->switchToIFrame(DisplayFormOnFrontendPage::$selectChangeArticle);
		$I->wait(2);
		$I->waitForElement(DisplayFormOnFrontendPage::$searchArticleId, 60);
		$I->fillField(DisplayFormOnFrontendPage::$searchArticleId, $articlesTitle);
		$I->waitForElement(DisplayFormOnFrontendPage::$searchIcon);
		$I->click(DisplayFormOnFrontendPage::$searchIcon);
		$I->wait(1);
		$I->click($articlesTitle);
		$I->wait(0.5);
		$I->switchToIFrame();
		$I->wait(2);
		$I->click(DisplayFormOnFrontendPage::$saveCloseButton);
		$I->waitForElement(DisplayFormOnFrontendPage::$alertMessage, 30, DisplayFormOnFrontendPage::$alertHead);
	}

	/**
	 * @param       $menu
	 * @param array $fillForm
	 * @throws Exception
	 */
	public function checkFormInFrontend($menu, $fillForm = array())
	{
		$I = $this;
		$I->amOnPage(DisplayFormOnFrontendPage::$frontendURL);
		$usePage = new DisplayFormOnFrontendPage();
		$I->waitForElement($usePage->xPathMenu($menu), 30);
		$I->click($usePage->xPathMenu($menu));
		$I->waitForElement(DisplayFormOnFrontendPage::$nameInput, 30);
		$I->fillField(DisplayFormOnFrontendPage::$nameInput, $fillForm['name']);
		$I->waitForElement(DisplayFormOnFrontendPage::$emailInput, 30);
		$I->fillField(DisplayFormOnFrontendPage::$emailInput, $fillForm['email']);
		$I->waitForElement(DisplayFormOnFrontendPage::$telephoneInput, 30);
		$I->fillField(DisplayFormOnFrontendPage::$telephoneInput, $fillForm['telephone']);
		$I->waitForElement(DisplayFormOnFrontendPage::$noteTextarea, 30);
		$I->fillField(DisplayFormOnFrontendPage::$noteTextarea, $fillForm['note']);
		$I->waitForElement(DisplayFormOnFrontendPage::$regularSubmit, 30);
		$I->click(DisplayFormOnFrontendPage::$regularSubmit);
		$I->waitForElement(DisplayFormOnFrontendPage::$alertMessage, 30, DisplayFormOnFrontendPage::$alertHead);
	}

	/**
	 * @param       $menu
	 * @param array $fillForm
	 * @throws Exception
	 */
	public function submitFormMissingEmail($menu, $fillForm = array())
	{
		$I = $this;
		$I->amOnPage(DisplayFormOnFrontendPage::$frontendURL);
		$usePage = new DisplayFormOnFrontendPage();
		$I->waitForElement($usePage->xPathMenu($menu), 30);
		$I->click($usePage->xPathMenu($menu));
		$I->waitForElement(DisplayFormOnFrontendPage::$nameInput, 30);
		$I->fillField(DisplayFormOnFrontendPage::$nameInput, $fillForm['name']);
		$I->waitForElement(DisplayFormOnFrontendPage::$telephoneInput, 30);
		$I->fillField(DisplayFormOnFrontendPage::$telephoneInput, $fillForm['telephone']);
		$I->waitForElement(DisplayFormOnFrontendPage::$noteTextarea, 30);
		$I->fillField(DisplayFormOnFrontendPage::$noteTextarea, $fillForm['note']);
		$I->waitForElement(DisplayFormOnFrontendPage::$regularSubmit, 30);
		$I->click(DisplayFormOnFrontendPage::$regularSubmit);
		$I->waitForText(DisplayFormOnFrontendPage::$messageError, 30, DisplayFormOnFrontendPage::$errorXpath);
	}

	/**
	 * @param       $menu
	 * @param array $fillForm
	 * @throws Exception
	 */
	public function submitFormMissingName($menu, $fillForm = array())
	{
		$I = $this;
		$I->amOnPage(DisplayFormOnFrontendPage::$frontendURL);
		$usePage = new DisplayFormOnFrontendPage();
		$I->waitForElement($usePage->xPathMenu($menu), 30);
		$I->click($usePage->xPathMenu($menu));
		$I->waitForElement(DisplayFormOnFrontendPage::$emailInput, 30);
		$I->fillField(DisplayFormOnFrontendPage::$emailInput, $fillForm['email']);
		$I->waitForElement(DisplayFormOnFrontendPage::$telephoneInput, 30);
		$I->fillField(DisplayFormOnFrontendPage::$telephoneInput, $fillForm['telephone']);
		$I->waitForElement(DisplayFormOnFrontendPage::$noteTextarea, 30);
		$I->fillField(DisplayFormOnFrontendPage::$noteTextarea, $fillForm['note']);
		$I->waitForElement(DisplayFormOnFrontendPage::$regularSubmit, 30);
		$I->click(DisplayFormOnFrontendPage::$regularSubmit);
		$I->waitForText(DisplayFormOnFrontendPage::$messageError, 30, DisplayFormOnFrontendPage::$errorXpath);
	}

	/**
	 * @param $menu
	 * @throws Exception
	 */
	public function checkFormWithHasExpired($menu)
	{
		$I = $this;
		$I->amOnPage(DisplayFormOnFrontendPage::$frontendURL);
		$usePage = new DisplayFormOnFrontendPage();
		$I->waitForElement($usePage->xPathMenu($menu), 30);
		$I->click($usePage->xPathMenu($menu));
		$I->waitForText(DisplayFormOnFrontendPage::$messageHasExpired, 30);
	}

	/**
	 * @param   string $menu
	 * @param   array $fillForm
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function checkFormCheckboxAndShowOnInFrontend($menu, $fillForm = array())
	{
		$I = $this;
		$I->amOnPage(DisplayFormOnFrontendPage::$frontendURL);
		$usePage = new DisplayFormOnFrontendPage();
		$I->waitForElement($usePage->xPathMenu($menu), 30);
		$I->click($usePage->xPathMenu($menu));
		$I->waitForJS("return window.jQuery && jQuery.active == 0;", 30);
		$I->waitForElement(DisplayFormOnFrontendPage::$nameInput, 30);
		$I->fillField(DisplayFormOnFrontendPage::$nameInput, $fillForm['name']);
		$I->waitForElement(DisplayFormOnFrontendPage::$emailInput, 30);
		$I->fillField(DisplayFormOnFrontendPage::$emailInput, $fillForm['email']);
		$I->waitForElementVisible($usePage->xPathCheckbox($fillForm['gender']), 30);
		$I->wait(0.5);
		$I->click($usePage->xPathCheckbox($fillForm['gender']));
		$I->waitForElementVisible(DisplayFormOnFrontendPage::$showOnTextAre, 60);
		$I->fillField(DisplayFormOnFrontendPage::$showOnTextAre, $fillForm['showon']);
		$I->waitForElementVisible(DisplayFormOnFrontendPage::$regularSubmit, 60);
		$I->click(DisplayFormOnFrontendPage::$regularSubmit);
		$I->waitForText(DisplayFormOnFrontendPage::$messageSubmit, 30, DisplayFormOnFrontendPage::$alertHead);
	}

	/**
	 * @param   string $menu
	 * @param   array $fillForm
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function checkFormRepeatEmail($menu, $fillForm = array())
	{
		$i = $this;
		$i->amOnPage(DisplayFormOnFrontendPage::$frontendURL);
		$usePage = new DisplayFormOnFrontendPage();
		$i->waitForElement($usePage->xPathMenu($menu), 30);
		$i->click($usePage->xPathMenu($menu));
		$i->waitForJS("return window.jQuery && jQuery.active == 0;", 30);
		$i->waitForElement(DisplayFormOnFrontendPage::$nameInput, 30);
		$i->fillField(DisplayFormOnFrontendPage::$nameInput, $fillForm['name']);
		$i->waitForElement(DisplayFormOnFrontendPage::$emailInput, 30);
		$i->fillField(DisplayFormOnFrontendPage::$emailInput, $fillForm['email']);
		$i->waitForElement(DisplayFormOnFrontendPage::$repeatEmailInput, 30);
		$i->fillField(DisplayFormOnFrontendPage::$repeatEmailInput, $fillForm['email']);
		$i->waitForJS("return window.jQuery && jQuery.active == 0;", 30);
		$i->waitForElementVisible(DisplayFormOnFrontendPage::$regularSubmit, 30);
		$i->click(DisplayFormOnFrontendPage::$regularSubmit);

		try
		{
			$i->waitForElement(DisplayFormOnFrontendPage::$alertMessage, 5, DisplayFormOnFrontendPage::$alertHead);
		}
		catch (Exception $e)
		{
			$i->waitForElementVisible(DisplayFormOnFrontendPage::$regularSubmit, 30);
			$i->click(DisplayFormOnFrontendPage::$regularSubmit);
			$i->waitForElement(DisplayFormOnFrontendPage::$alertMessage, 30, DisplayFormOnFrontendPage::$alertHead);
		}
	}

 /**
	 * @param   string $menu
	 * @param   array $fillForm
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function checkFormMultiSelectAndShowOnInFrontend($menu, $fillForm = array())
	{
		$i = $this;
		$i->amOnPage(DisplayFormOnFrontendPage::$frontendURL);
		$usePage = new DisplayFormOnFrontendPage();
		$i->waitForElement($usePage->xPathMenu($menu), 30);
		$i->click($usePage->xPathMenu($menu));
		$i->waitForJS("return window.jQuery && jQuery.active == 0;", 30);
		$i->waitForElement(DisplayFormOnFrontendPage::$nameInput, 30);
		$i->fillField(DisplayFormOnFrontendPage::$nameInput, $fillForm['name']);
		$i->waitForElement(DisplayFormOnFrontendPage::$emailInput, 30);
		$i->fillField(DisplayFormOnFrontendPage::$emailInput, $fillForm['email']);
		$i->waitForElementVisible($usePage->xpathMultiSelect($fillForm['province']), 30);
		$i->click($usePage->xpathMultiSelect($fillForm['province']));
		$i->waitForElementVisible(DisplayFormOnFrontendPage::$showOnTextAre, 30);
		$i->fillField(DisplayFormOnFrontendPage::$showOnTextAre, $fillForm['showon']);
		$i->waitForElement(DisplayFormOnFrontendPage::$regularSubmit, 30);
		$i->click(DisplayFormOnFrontendPage::$regularSubmit);

		try
		{
			$i->waitForElement(DisplayFormOnFrontendPage::$alertMessage, 5, DisplayFormOnFrontendPage::$alertHead);
		}
		catch (Exception $e)
		{
			$i->waitForElementVisible(DisplayFormOnFrontendPage::$regularSubmit, 30);
			$i->click(DisplayFormOnFrontendPage::$regularSubmit);
			$i->waitForElement(DisplayFormOnFrontendPage::$alertMessage, 30, DisplayFormOnFrontendPage::$alertHead);
		}
	}

	/**
	 * @param $menu
	 * @param $notificationMessage
	 * @param array $fillForm
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function checkFormWithNotificationInFrontend($menu, $notificationMessage, $fillForm = array())
	{
		$i = $this;
		$i->amOnPage(DisplayFormOnFrontendPage::$frontendURL);
		$usePage = new DisplayFormOnFrontendPage();
		$i->waitForElement($usePage->xPathMenu($menu), 30);
		$i->click($usePage->xPathMenu($menu));
		$i->waitForElement(DisplayFormOnFrontendPage::$nameInput, 30);
		$i->fillField(DisplayFormOnFrontendPage::$nameInput, $fillForm['name']);
		$i->waitForElement(DisplayFormOnFrontendPage::$emailInput, 30);
		$i->fillField(DisplayFormOnFrontendPage::$emailInput, $fillForm['email']);
		$i->waitForElement(DisplayFormOnFrontendPage::$telephoneInput, 30);
		$i->fillField(DisplayFormOnFrontendPage::$telephoneInput, $fillForm['telephone']);
		$i->waitForElement(DisplayFormOnFrontendPage::$regularSubmit, 30);
		$i->click(DisplayFormOnFrontendPage::$regularSubmit);
		$i->waitForText($notificationMessage, 30);
	}

	/**
	 * @param $menu
	 * @param array $fillForm
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function checkFormWithDateInFrontend($menu, $fillForm = array())
	{
		$i = $this;
		$i->amOnPage(DisplayFormOnFrontendPage::$frontendURL);
		$usePage = new DisplayFormOnFrontendPage();
		$i->waitForElement($usePage->xPathMenu($menu), 30);
		$i->click($usePage->xPathMenu($menu));
		$i->waitForElement(DisplayFormOnFrontendPage::$nameInput, 30);
		$i->fillField(DisplayFormOnFrontendPage::$nameInput, $fillForm['name']);
		$i->waitForElement(DisplayFormOnFrontendPage::$emailInput, 30);
		$i->fillField(DisplayFormOnFrontendPage::$emailInput, $fillForm['email']);
		$i->waitForElement(DisplayFormOnFrontendPage::$telephoneInput, 30);
		$i->fillField(DisplayFormOnFrontendPage::$telephoneInput, $fillForm['telephone']);
		$i->waitForElement(DisplayFormOnFrontendPage::$dateInput, 30);
		$i->fillField(DisplayFormOnFrontendPage::$dateInput, $fillForm['date']);
		$i->waitForElement(DisplayFormOnFrontendPage::$regularSubmit, 30);
		$i->click(DisplayFormOnFrontendPage::$regularSubmit);
		$i->waitForElement(DisplayFormOnFrontendPage::$alertMessage, 30, DisplayFormOnFrontendPage::$alertHead);
	}
}
