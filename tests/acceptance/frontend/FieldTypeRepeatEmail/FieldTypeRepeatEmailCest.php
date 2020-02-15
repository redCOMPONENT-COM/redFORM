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
 * Class FieldTypeRepeatEmailCest
 * @since 3.3.28
 */
class FieldTypeRepeatEmailCest
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
	 * FieldTypeRepeatEmailCest constructor.
	 * @since 3.3.28
	 */
	public function __construct()
	{
		$this->faker   = Faker\Factory::create();

		$this->nameField =
			[
				'name'        => 'Name',
				'fieldtype'   => 'Full name',
				'placeholder' => 'Please enter your name'
			];

		$this->emailField =
			[
				'name'        => 'Email',
				'fieldtype'   => 'E-mail',
				'placeholder' => 'Please enter your email'
			];

		$this->repeatField =
			[
				'name'        => 'Repeat Email',
				'fields'      => 'Repeat Email',
				'fieldtype'   => 'Repeat text',
				'placeholder' => 'Please enter your email again',
				'targetField' => 'Email',
				'required'    => 'No',
				'formExpires' => 'No',
				'section'     => 'general',
			];

		$this->articlesTitle = $this->faker->bothify('Article ?##?');
		$this->articles      = 'Single Article';
		$this->menuTitle     = $this->faker->bothify('Menu ?##?');
		$this->menuItemType  = 'Articles';

		$this->fillForm =
			[
				'name'              => $this->faker->bothify('Name ?##?'),
				'email'             => $this->faker->email,
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
	 * @param AcceptanceTester $i
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function _before(AcceptanceTester $i)
	{
		$i->doAdministratorLogin("admin", "admin", null);
	}

	/**
	 * @param AddAFormSteps $i
	 * @param $scenario
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function createForm(AddAFormSteps $i, $scenario)
	{
		$i = new AddAFieldSteps($scenario);
		$i->createField($this->nameField, 'save&close');
		$i->createField($this->emailField, 'save&close');
		$i->createFieldRepeat($this->repeatField);
		$i = new AddAFormSteps($scenario);
		$i->wantToTest('Create form for check.');
		$i->createForm($this->paramsForm, 'save');
		$i->editAndAddFieldForForm($this->paramsForm['name'], $this->repeatField);
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
		$i->checkFormRepeatEmail($this->menuTitle, $this->fillForm);
	}

	/**
	 * @param DisplayFormOnFrontendSteps $i
	 * @param                            $scenario
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function clearAll(DisplayFormOnFrontendSteps $i, $scenario)
	{
		$i->wantTo('Clear up');
		$i = new AddAFormSteps($scenario);
		$i->deleteFormHasSubmitters($this->paramsForm['name']);

		$i = new AddAFieldSteps($scenario);
		$i->deleteAllField();
	}
}
