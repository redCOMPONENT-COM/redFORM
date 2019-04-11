<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2019 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance;

use Page\Acceptance\DisplayFormOnFrontendPage as DisplayFormOnFrontendPage;
use Page\Acceptance\AddAFormPage as AddAFormPage;

class DisplayFormOnFrontendSteps  extends Adminredform
{
	/**
	 * @param                       $formName
	 * @param                       $articlesTitle
	 * @param \Codeception\Scenario $scenario
	 * @throws \Exception
	 */
	public function createNewArticle($formName, $articlesTitle, \Codeception\Scenario $scenario)
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
	 * @throws \Exception
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
		$I->click($menuItemType);
		$usePage = new DisplayFormOnFrontendPage();
		$I->scrollTo($usePage->returnMenuItem($articles));
		$I->waitForElement($usePage->returnMenuItem($articles), 30);
		$I->click($usePage->returnMenuItem($articles));
		$I->waitForElement(DisplayFormOnFrontendPage::$selectArticleLbl, 30);
		$I->waitForElement(DisplayFormOnFrontendPage::$selectArticle, 30);
		$I->click(DisplayFormOnFrontendPage::$selectArticle);
		$I->switchToIFrame(DisplayFormOnFrontendPage::$selectChangeArticle);
		$I->waitForElement(DisplayFormOnFrontendPage::$searchArticleId, 30);
		$I->fillField(DisplayFormOnFrontendPage::$searchArticleId, $articlesTitle);
		$I->waitForElement(DisplayFormOnFrontendPage::$searchIcon);
		$I->click(DisplayFormOnFrontendPage::$searchIcon);
		$I->click($articlesTitle);
		$I->wait(0.5);
		$I->switchToIFrame();
		$I->click(DisplayFormOnFrontendPage::$saveCloseButton);
		$I->waitForElement(DisplayFormOnFrontendPage::$alertMessage, 30, DisplayFormOnFrontendPage::$alertHead);
	}

	/**
	 * @param       $menu
	 * @param array $fillForm
	 * @throws \Exception
	 */
	public function checkFormInFrontend($menu, $fillForm = array())
	{
		$I = $this;
		$I->amOnPage(DisplayFormOnFrontendPage::$frontendURL);
		$usepage = new DisplayFormOnFrontendPage();
		$I->waitForElement($usepage->xPathMenu($menu), 30);
		$I->click($usepage->xPathMenu($menu));
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
	 * @throws \Exception
	 */
	public function submitFormMissingEmail($menu, $fillForm = array())
	{
		$I = $this;
		$I->amOnPage(DisplayFormOnFrontendPage::$frontendURL);
		$usepage = new DisplayFormOnFrontendPage();
		$I->waitForElement($usepage->xPathMenu($menu), 30);
		$I->click($usepage->xPathMenu($menu));
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
	 * @throws \Exception
	 */
	public function submitFormMissingName($menu, $fillForm = array())
	{
		$I = $this;
		$I->amOnPage(DisplayFormOnFrontendPage::$frontendURL);
		$usepage = new DisplayFormOnFrontendPage();
		$I->waitForElement($usepage->xPathMenu($menu), 30);
		$I->click($usepage->xPathMenu($menu));
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
	 * @throws \Exception
	 */
	public function checkFormWithHasExpired($menu)
	{
		$I = $this;
		$I->amOnPage(DisplayFormOnFrontendPage::$frontendURL);
		$usepage = new DisplayFormOnFrontendPage();
		$I->waitForElement($usepage->xPathMenu($menu), 30);
		$I->click($usepage->xPathMenu($menu));
		$I->waitForText(DisplayFormOnFrontendPage::$messageHasExpired, 30);
	}
}