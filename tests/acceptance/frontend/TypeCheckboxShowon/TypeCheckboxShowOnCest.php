<?php
/**
 * @package     redFORM
 * @subpackage  Cest
 * @copyright   Copyright (C) 2008 - 2019 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Acceptance\AddAFieldSteps as AddAFieldSteps;
use Step\Acceptance\AddAFormSteps as AddAFormSteps;
use Step\Acceptance\DisplayFormOnFrontendSteps as DisplayFormOnFrontendSteps;

/**
 * Class TypeCheckboxShowOnCest
 * @since 3.3.28
 */
class TypeCheckboxShowOnCest
{
	/**
	 * @var \Faker\Generator
	 * @since 3.3.28
	 */
	protected $faker;

	/**
	 * @var array
	 * @since 3.3.28
	 */
	protected $nameField;

	/**
	 * @var array
	 * @since 3.3.28
	 */
	protected $emailField;

	/**
	 * @var array
	 * @since 3.3.28
	 */
	protected $genderField;

	/**
	 * @var array
	 * @since 3.3.28
	 */
	protected $showOnField;

	/**
	 * @var array
	 * @since 3.3.28
	 */
	protected $optionValue;

	/**
	 * @var string
	 * @since 3.3.28
	 */
	protected $articlesTitle;

	/**
	 * @var string
	 * @since 3.3.28
	 */
	protected $articles;

	/**
	 * @var string
	 * @since 3.3.28
	 */
	protected $menuTitle;

	/**
	 * @var string
	 * @since 3.3.28
	 */
	protected $menuItemType;

	/**
	 * @var array
	 * @since 3.3.28
	 */
	protected $fillForm;

	/**
	 * @var array
	 * @since 3.3.28
	 */
	protected $paramsForm;

	/**
	 * TypeCheckboxShowOnCest constructor.
	 * @since 3.3.28
	 */
	public function __construct()
	{
		$this->faker   = Faker\Factory::create();

		$this->nameField =
			[
				'name'       => 'Name',
				'fieldtype'  => 'Full name',
				'placeholder'=> 'Please enter your name'
			];

		$this->emailField =
			[
				'name'              => 'Email',
				'fieldtype'         => 'E-mail',
				'placeholder'       => 'Please enter your email'
			];

		$this->genderField =
			[
				'name'              => 'gender',
				'fields'              => 'gender',
				'fieldtype'         => 'Checkbox',
				'required'          => 'No',
				'formExpires'       => 'No',
				'section'           => 'general',
			];

		$this->showOnField =
			[
				'name'              => 'Show on',
				'fields'            => 'Show on',
				'fieldtype'         => 'Textarea',
				'placeholder'       => 'Please enter your show on',
				'required'          => 'No',
				'formExpires'       => 'No',
				'section'           => 'general',
			];

		$this->optionValue =  array(
			array(
				"value" => "male",
				"label" => "male"
			),
			array(
				"value" => "female",
				"label" => "female"
			)
		);


		$this->articlesTitle = $this->faker->bothify('Article ?##?');
		$this->articles      = 'Single Article';
		$this->menuTitle     = $this->faker->bothify('Menu ?##?');
		$this->menuItemType  = 'Articles';

		$this->fillForm =
			[
				'name'              => $this->faker->bothify('Name ?##?'),
				'email'             => $this->faker->email,
				'gender'            => $this->optionValue[0][value],
				'showon'            => $this->faker->bothify('Name ????????????????'),
			];

		$this->paramsForm =
			[
				'name'              => $this->faker->bothify('FormSave ?##?'),
				'fields_1'          => $this->nameField['name'],
				'section_1'         => 'general',
				'fields_2'          => $this->emailField['name'],
				'section_2'         => 'general',
				'required'          => 'No',
				'formExpires'       => 'No'
			];

	}

	/**
	 * @param AcceptanceTester $I
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function _before(AcceptanceTester $I)
	{
		$I->doAdministratorLogin("admin", "admin", null);
	}

	/**
	 * @param AddAFormSteps $I
	 * @param $scenario
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function createForm(AddAFormSteps $I, $scenario)
	{
		$I = new AddAFieldSteps($scenario);
		$I->createField($this->nameField, 'save&close');
		$I->createField($this->emailField, 'save&close');
		$I->createField($this->genderField, 'save&close');

		$IDFieldGender = $I->getFieldID($this->genderField['name']);
		$this->showOnField['showon'] = $IDFieldGender.":".$this->optionValue[0]['value'].",".$this->optionValue[1]['value'];

		$I->createField($this->showOnField, 'save&close');
		$I->addOptionFieldCheckbox("gender",$this->optionValue );

		$I = new AddAFormSteps($scenario);
		$I->wantToTest('Create form for check.');
		$I->createForm($this->paramsForm, 'save');
		$I->editAndAddFieldForForm($this->paramsForm['name'], $this->genderField);
		$I->editAndAddFieldForForm($this->paramsForm['name'], $this->showOnField);
	}

	/**
	 * @param DisplayFormOnFrontendSteps $I
	 * @param $scenario
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function checkFormCheckboxAndShowOnInFrontend(DisplayFormOnFrontendSteps $I, $scenario)
	{
		$I->wantTo('Create new article');
		$I->createNewArticle($this->paramsForm['name'], $this->articlesTitle, $scenario);
		$I->wantTo('Create new menu items');
		$I->createNewMenuItem($this->articlesTitle, $this->articles, $this->menuTitle, $this->menuItemType, 'Main Menu');
		$I->wantTo('Check form display in frontend');
		$I->checkFormCheckboxAndShowOnInFrontend($this->menuTitle, $this->fillForm);
	}

	/**
	 * @param DisplayFormOnFrontendSteps $I
	 * @param                            $scenario
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function clearAll(DisplayFormOnFrontendSteps $I, $scenario)
	{
		$I->wantTo('Clear up');
		$I = new AddAFormSteps($scenario);
		$I->deleteFormHasSubmitters($this->paramsForm['name']);

		$I = new AddAFieldSteps($scenario);
		$I->deleteAllField();
	}
}
