<?php
/**
 * @package     redFORM
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2019 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Acceptance\AddAFieldSteps as AddAFieldSteps;
use Step\Acceptance\AddAFormSteps as AddAFormSteps;
use Step\Acceptance\DisplayFormOnFrontendSteps as DisplayFormOnFrontendSteps;

/**
 * Class FieldTypeDateCest
 * @since 3.3.28
 */
class FieldTypeDateCest
{
	/**
	 * @var   string
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
	protected $telephoneField;

	/**
	 * @var array
	 * @since 3.3.28
	 */
	protected $dateField;

	/**
	 * @var array
	 * @since 3.3.28
	 */
	protected $paramsForm;

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
	 * FieldTypeDateCest constructor.
	 * @since 3.3.28
	 */
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
				'fields'            => 'Telephone',
				'fieldtype'         => 'Textfield',
				'placeholder'       => 'Please enter your telephone',
				'required'          => 'No',
				'formExpires'       => 'No',
				'section'           => 'general',
			];

		$this->dateField =
			[
				'name'              => 'BirthDay',
				'fields'            => 'BirthDay',
				'fieldtype'         => 'Date',
				'placeholder'       => 'Please enter your birth day',
				'required'          => 'No',
				'formExpires'       => 'No',
				'section'           => 'general',
			];

		$this->paramsForm =
			[
				'name'              => $this->faker->bothify('FormSave ?##?'),
				'fields_1'          => $this->nameField['name'],
				'section_1'         => 'general',
				'fields_2'          => $this->emailField['name'],
				'section_2'         => 'general',
				'required'          => 'Yes'
			];


		$this->articlesTitle        = $this->faker->bothify('Article ?##?');
		$this->articles             = 'Single Article';
		$this->menuTitle            = $this->faker->bothify('Menu ?##?');
		$this->menuItemType         = 'Articles';

		$this->fillForm =
			[
				'name'              => $this->faker->bothify('Name ?##?'),
				'email'             => $this->faker->email,
				'telephone'         => $this->faker->phoneNumber,
				'date'              => $this->faker->date(),
			];

	}

	/**
	 * @param AcceptanceTester $i
	 * @throws Exception
	 *  @since 3.3.28
	 */
	public function _before(AcceptanceTester $i)
	{
		$i->doAdministratorLogin("admin", "admin", null);
	}

	/**
	 * @param AddAFormSteps $i
	 * @param               $scenario
	 * @throws Exception
	 *  @since 3.3.28
	 */
	public function createForm(AddAFormSteps $i, $scenario)
	{
		$i = new AddAFieldSteps($scenario);
		$i->createField($this->nameField, 'save&close');
		$i->createField($this->emailField, 'save&close');
		$i->createField($this->telephoneField, 'save&close');
		$i->createField($this->dateField, 'save&close');

		$i = new AddAFormSteps($scenario);
		$i->wantToTest('Create form for check.');
		$i->createForm($this->paramsForm, 'save');
		$i->editAndAddFieldForForm($this->paramsForm['name'], $this->telephoneField);
		$i->editAndAddFieldForForm($this->paramsForm['name'], $this->dateField);
	}

	/**
	 * @param DisplayFormOnFrontendSteps $i
	 * @param                            $scenario
	 * @throws Exception
	 *  @since 3.3.28
	 */
	public function checkDisplayForm(DisplayFormOnFrontendSteps $i, $scenario)
	{
		$i = new DisplayFormOnFrontendSteps($scenario);
		$i->wantTo('Create new article');
		$i->createNewArticle($this->paramsForm['name'], $this->articlesTitle, $scenario);
		$i->wantTo('Create new menu items');
		$i->createNewMenuItem($this->articlesTitle, $this->articles, $this->menuTitle, $this->menuItemType, 'Main Menu');
		$i->wantTo('Check form display in frontend');
		$i->checkFormWithDateInFrontend($this->menuTitle, $this->fillForm);
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
