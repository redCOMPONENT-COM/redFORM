<?php
/**
 * @package     redFORM
 * @subpackage  Cest TypeCheckboxShowOn
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Acceptance\AddAFieldSteps;
use Step\Acceptance\AddAFormSteps;
use Step\Acceptance\DisplayFormOnFrontendSteps;

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
			array(
				'name'        => 'Name',
				'fieldtype'   => 'Full name',
				'placeholder' => 'Please enter your name'
			);

		$this->emailField =
			array(
				'name'        => 'Email',
				'fieldtype'   => 'E-mail',
				'placeholder' => 'Please enter your email'
			);

		$this->genderField =
			array(
				'name'        => 'gender',
				'fields'      => 'gender',
				'fieldtype'   => 'Checkbox',
				'required'    => 'No',
				'formExpires' => 'No',
				'section'     => 'general',
			);

		$this->showOnField =
			array(
				'name'        => 'Show on',
				'fields'      => 'Show on',
				'fieldtype'   => 'Textarea',
				'placeholder' => 'Please enter your show on',
				'required'    => 'No',
				'formExpires' => 'No',
				'section'     => 'general',
			);

		$this->optionValue = array(
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
			array(
				'name'              => $this->faker->bothify('Name ?##?'),
				'email'             => $this->faker->email,
				'gender'            => $this->optionValue[0][value],
				'showon'            => $this->faker->bothify('Name ????????????????'),
			);

		$this->paramsForm =
			array(
				'name'              => $this->faker->bothify('FormSave ?##?'),
				'fields_1'          => $this->nameField['name'],
				'section_1'         => 'general',
				'fields_2'          => $this->emailField['name'],
				'section_2'         => 'general',
				'required'          => 'No',
				'formExpires'       => 'No'
			);
	}

	/**
	 * @param AcceptanceTester $i
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function _before(AcceptanceTester $i)
	{
		$i->doAdministratorLogin("admin", "admin", null);
	}

	/**
	 * @param AddAFieldSteps $i
	 * @param $scenario
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function createForm(AddAFieldSteps $i, $scenario)
	{
		$i->createField($this->nameField, 'save&close');
		$i->createField($this->emailField, 'save&close');
		$i->createField($this->genderField, 'save&close');

		$iDFieldGender = $i->getFieldID($this->genderField['name']);
		$this->showOnField['showon'] = $iDFieldGender . ":" . $this->optionValue[0]['value'] . "," . $this->optionValue[1]['value'];

		$i->createField($this->showOnField, 'save&close');
		$i->addOptionFieldCheckbox($this->genderField['name'], $this->optionValue);

		$i = new AddAFormSteps($scenario);
		$i->wantToTest('Create form for check.');
		$i->createForm($this->paramsForm, 'save');
		$i->editAndAddFieldForForm($this->paramsForm['name'], $this->genderField);
		$i->editAndAddFieldForForm($this->paramsForm['name'], $this->showOnField);
	}

	/**
	 * @param DisplayFormOnFrontendSteps $i
	 * @param $scenario
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function checkFormCheckboxAndShowOnInFrontend(DisplayFormOnFrontendSteps $i, $scenario)
	{
		$i->wantTo('Create new article');
		$i->createNewArticle($this->paramsForm['name'], $this->articlesTitle, $scenario);
		$i->wantTo('Create new menu items');
		$i->createNewMenuItem($this->articlesTitle, $this->articles, $this->menuTitle, $this->menuItemType, 'Main Menu');
		$i->wantTo('Check form display in frontend');
		$i->checkFormCheckboxAndShowOnInFrontend($this->menuTitle, $this->fillForm);
	}

	/**
	 * @param DisplayFormOnFrontendSteps $i
	 * @param                            $scenario
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function cleanUp(DisplayFormOnFrontendSteps $i, $scenario)
	{
		$i->wantTo('Clear up');
		$i = new AddAFormSteps($scenario);
		$i->deleteFormHasSubmitters($this->paramsForm['name']);

		$i = new AddAFieldSteps($scenario);
		$i->deleteAllField();
	}
}
