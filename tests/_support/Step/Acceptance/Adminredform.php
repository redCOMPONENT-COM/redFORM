<?php
namespace Step\Acceptance;

use Page\Acceptance\AddAFieldPage;
use Page\Acceptance\AddAFormPage;
use Page\Acceptance\AddASectionPage;
use Page\Acceptance\RedFormAdminPage;

class Adminredform extends \AcceptanceTester
{
	/**
	 * Create a section
	 *
	 * @param   array  $params  section fields
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function createSectionIfNotExists($params, $scenario)
	{
		$I = $this;
		$I->amOnPage(AddASectionPage::$URL);
		$I->waitForText(AddASectionPage::$section, 30, AddASectionPage::$headPage);

		$user = new AddASectionPage();
		if ($I->isElementPresent($user->sectionItem($params['name'])))
		{
			return;
		}

		$I = new AddASectionSteps($scenario);
		$I->createSection($params);
	}

	/**
	 * Create a field if it doesn't already exists
	 *
	 * @param   array  $params  section fields
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function createFieldIfNotExists($params, $scenario)
	{
		$I = $this;
		$I->amOnPage(AddAFieldPage::$URL);
		$I->waitForText(AddAFieldPage::$field, 30, AddAFieldPage::$headPage);

		$user = new AddAFieldPage();
		if ($I->isElementPresent($user->fieldList($params['name'])))
		{
			return;
		}

		$I = new AddAFieldSteps($scenario);
		$I->createField($params);
	}

	/**
	 * Create a Form if doesn't exist
	 *
	 * @param   array  $params  section fields
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function createFormIfNotExists($params, $scenario)
	{
		$I = $this;
		$I->amOnPage(AddAFormPage::$url);
		$I->waitForText(AddAFormPage::$form, 30, AddAFormPage::$headPage);

		$user = new AddAFormPage();
		if ($I->isElementPresent($user->formList($params['name'])))
		{
			return;
		}
		$I = new AddAFormSteps($scenario);
		$I->createForm($params);
	}

	/**
	 * Return true if element was found on page
	 *
	 * @param   string  $element  element descriptor
	 *
	 * @return bool
	 */

	protected function isElementPresent($element)
	{
		$I = $this;

		try
		{
			$I->seeElement($element);
		}
		catch (\PHPUnit_Framework_AssertionFailedError $f)
		{
			return false;
		}

		return true;
	}

	/**
	 * @param string $id
	 * @param string $value
	 * @throws \Exception
	 */
	public function selectOptionInChosenXpath($id, $value)
	{
		$I = $this;
		$user = new RedFormAdminPage();
		$I->waitForElement($user->selectXpath($id), 30);
		$I->scrollTo($user->selectXpath($id));
		$I->wait(0.5);
		$I->click($user->selectXpath($id));
		$I->wait(0.5);
		$I->waitForElement($user->selectXpathValue($id, $value), 30);
		$I->scrollTo($user->selectXpathValue($id, $value));
		$I->wait(0.5);
		$I->click($user->selectXpathValue($id, $value));
	}

	/**
	 * @throws \Exception
	 */
	public function configurationEmailSystem($option)
	{
		$I = $this;
		$I->amOnPage(RedFormAdminPage::$urlSystem);
		$I->waitForElement(RedFormAdminPage::$server, 30);
		$I->click(RedFormAdminPage::$server);
		$I->scrollTo(RedFormAdminPage::$mailSetting);
		$I->selectOptionInRadioField(RedFormAdminPage::$sendMail, $option);
		$I->click(RedFormAdminPage::$saveButton);
		$I->waitForElement(AddAFieldPage::$alertMessage, 30, AddAFieldPage::$alertHead);
	}
}
