<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2019 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Acceptance\AddAFormSteps as AddAFormSteps;
use Step\Acceptance\AddAFieldSteps as AddAFieldSteps;
use Step\Acceptance\DisplayFormOnFrontendSteps as DisplayFormOnFrontendSteps;
class DisplayFormOnFrontendCest
{
	public function __construct()
	{
		$this->faker   = Faker\Factory::create();
		$this->nameField =
			[
				'name'              => 'Name',
				'fieldtype'         => 'Full name',
				'placeholder'       => 'Please enter your name'
			];

		$this->emailField =
			[
				'name'              => 'Email',
				'fieldtype'         => 'E-mail',
				'placeholder'       => 'Please enter your email'
			];

		$this->telephoneField =
			[
				'name'              => 'Telephone',
				'fieldtype'         => 'Textfield',
				'placeholder'       => 'Please enter your telephone'
			];

		$this->noteField =
			[
				'name'              => 'Note',
				'fieldtype'         => 'Textarea',
				'placeholder'       => 'Please enter your note'
			];

		$this->paramsForm =
			[
				'name' => $this->faker->bothify('FormSave ?##?'),
				'fields_1' => $this->nameField['name'],
				'section_1' => 'general',
				'fields_2' => $this->emailField['name'],
				'section_2' => 'general',
				'required'  => 'Yes'
			];

		$this->telephoneForm =
			[
				'fields' => $this->telephoneField['name'],
				'section' => 'general',
				'required'  => 'No',
				'formExpires' => 'No'
			];

		$this->noteForm =
			[
				'fields' => $this->noteField['name'],
				'section' => 'general',
				'required'  => 'No',
				'formExpires' => 'No'
			];

		$this->articlesTitle = $this->faker->bothify('Article ?##?');
		$this->articles = 'Single Article';
		$this->menuTitle = $this->faker->bothify('Menu ?##?');
		$this->menuItemType = 'Articles';

		$this->fillForm =
			[
				'name'          => $this->faker->bothify('Name ?##?'),
				'email'         => $this->faker->email,
				'telephone'     => $this->faker->phoneNumber,
				'note'          => $this->faker->bothify('Name ????????????????'),
			];
	}
	/**
	 * @param AcceptanceTester $I
	 * @throws Exception
	 */
	public function _before(AcceptanceTester $I)
	{
		$I->doAdministratorLogin();
	}

	/**
	 * @param AddAFormSteps $I
	 * @param               $scenario
	 * @throws Exception
	 */
	public function createForm(AddAFormSteps $I, $scenario)
	{
		$I = new AddAFieldSteps($scenario);
		$I->wantToTest('Create field for check.');
		$I->createField($this->nameField, 'save&close');
		$I->createField($this->emailField, 'save&close');
		$I->createField($this->telephoneField, 'save&close');
		$I->createField($this->noteField, 'save&close');

		$I = new AddAFormSteps($scenario);
		$I->wantToTest('Create form for check.');
		$I->createForm($this->paramsForm, 'save');
		$I->editAndAddFieldForForm($this->paramsForm['name'], $this->telephoneForm);
		$I->editAndAddFieldForForm($this->paramsForm['name'], $this->noteForm);
	}

	/**
	 * @param DisplayFormOnFrontendSteps $I
	 * @param                            $scenario
	 * @throws Exception
	 */
	public function checkDisplayForm(DisplayFormOnFrontendSteps $I, $scenario)
	{
		$I = new DisplayFormOnFrontendSteps($scenario);
		$I->createNewArticle($this->paramsForm['name'], $this->articlesTitle, $scenario);
		$I->createNewMenuItem($this->articlesTitle, $this->articles, $this->menuTitle, $this->menuItemType);
		$I->checkFormInFrontend($this->menuTitle, $this->fillForm);
	}
}