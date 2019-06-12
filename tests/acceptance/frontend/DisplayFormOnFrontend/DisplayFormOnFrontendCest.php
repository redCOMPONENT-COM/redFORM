<?php
/**
 * @package     redFORM
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2019 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Acceptance\AddAFormSteps as AddAFormSteps;
use Step\Acceptance\AddAFieldSteps as AddAFieldSteps;
use Step\Acceptance\DisplayFormOnFrontendSteps as DisplayFormOnFrontendSteps;
class DisplayFormOnFrontendCest
{
	/**
	 * @var   string
	 */
	protected $faker;

	/**
	 * @var array
	 */
	protected $nameField;

	/**
	 * @var array
	 */
	protected $emailField;

	/**
	 * @var array
	 */
	protected $telephoneField;

	/**
	 * @var array
	 */
	protected $noteField;

	/**
	 * @var array
	 */
	protected $paramsForm;

	/**
	 * @var array
	 */
	protected $telephoneForm;

	/**
	 * @var array
	 */
	protected $noteForm;

	/**
	 * @var string
	 */
	protected $articlesTitle;

	/**
	 * @var string
	 */
	protected $articles;

	/**
	 * @var string
	 */
	protected $menuTitle;

	/**
	 * @var string
	 */
	protected $menuItemType;
	
	/**
	 * @var array
	 */
	protected $fillForm;

	/**
	 * DisplayFormOnFrontendCest constructor.
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
				'name'              => $this->faker->bothify('FormSave ?##?'),
				'fields_1'          => $this->nameField['name'],
				'section_1'         => 'general',
				'fields_2'          => $this->emailField['name'],
				'section_2'         => 'general',
				'required'          => 'Yes'
			];

		$this->telephoneForm =
			[
				'fields'            => $this->telephoneField['name'],
				'section'           => 'general',
				'required'          => 'No',
				'formExpires'       => 'No'
			];

		$this->noteForm =
			[
				'fields'            => $this->noteField['name'],
				'section'           => 'general',
				'required'          => 'No',
				'formExpires'       => 'No'
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
				'note'              => $this->faker->bothify('Name ????????????????'),
			];

		$this->paramsFormExpires = array();
		$this->paramsFormExpires['startDate']   = '2019-04-02';
		$this->paramsFormExpires['endDate']     = '2022-04-02';
		$this->paramsFormExpires['formExpires'] = 'Yes';

		$this->paramsFormHasExpires = array();
		$this->paramsFormHasExpires['startDate']    = '2019-03-02';
		$this->paramsFormHasExpires['endDate']      = '2019-04-02';
		$this->paramsFormHasExpires['formExpires']  = 'Yes';
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
//	public function createForm(AddAFormSteps $I, $scenario)
//	{
//		$I = new AddAFieldSteps($scenario);
//		$I->createField($this->nameField, 'save');
//		$I->createField($this->emailField, 'save');
//		$I->createField($this->telephoneField, 'save');
//		$I->createField($this->noteField, 'save');
//
//		$I = new AddAFormSteps($scenario);
//		$I->wantToTest('Create form for check.');
//		$I->createForm($this->paramsForm, 'save');
//		$I->editAndAddFieldForForm($this->paramsForm['name'], $this->telephoneForm);
//		$I->editAndAddFieldForForm($this->paramsForm['name'], $this->noteForm);
//	}

	/**
	 * @param DisplayFormOnFrontendSteps $I
	 * @param                            $scenario
	 * @throws Exception
	 */
	public function checkDisplayForm(DisplayFormOnFrontendSteps $I, $scenario)
	{
		$I = new DisplayFormOnFrontendSteps($scenario);
		$I->wantTo('Create new article');
		$I->createNewArticle($this->paramsForm['name'], $this->articlesTitle, $scenario);
		$I->wantTo('Create new menu items');
		$I->createNewMenuItem($this->articlesTitle, $this->articles, $this->menuTitle, $this->menuItemType, 'Main Menu');
		$I->wantTo('Check form display in frontend');
		$I->checkFormInFrontend($this->menuTitle, $this->fillForm);
	}

	/**
	 * @param DisplayFormOnFrontendSteps $I
	 * @param                            $scenario
	 * @throws Exception
	 */
//	public function submitFormMissingEmail(DisplayFormOnFrontendSteps $I, $scenario)
//	{
//		$I = new DisplayFormOnFrontendSteps($scenario);
//		$I->wantTo('Create new article');
//		$I->createNewArticle($this->paramsForm['name'], $this->articlesTitle, $scenario);
//		$I->wantTo('Create new menu items');
//		$I->createNewMenuItem($this->articlesTitle, $this->articles, $this->menuTitle, $this->menuItemType, 'Main Menu');
//		$I->wantToTest('Submit form with missing email');
//		$I->submitFormMissingEmail($this->menuTitle, $this->fillForm);
//	}
//
//	/**
//	 * @param DisplayFormOnFrontendSteps $I
//	 * @param                            $scenario
//	 * @throws Exception
//	 */
//	public function submitFormMissingName(DisplayFormOnFrontendSteps $I, $scenario)
//	{
//		$I = new DisplayFormOnFrontendSteps($scenario);
//		$I->wantTo('Create new article');
//		$I->createNewArticle($this->paramsForm['name'], $this->articlesTitle, $scenario);
//		$I->wantTo('Create new menu items');
//		$I->createNewMenuItem($this->articlesTitle, $this->articles, $this->menuTitle, $this->menuItemType, 'Main Menu');
//		$I->wantToTest('Submit form with missing name');
//		$I->submitFormMissingName($this->menuTitle, $this->fillForm);
//	}
//
//	/**
//	 * @param DisplayFormOnFrontendSteps $I
//	 * @param                            $scenario
//	 * @throws Exception
//	 */
//	public function checkFormWithHasExpired(DisplayFormOnFrontendSteps $I, $scenario)
//	{
//		$I->wantTo('Edit form with expires');
//		$I = new AddAFormSteps($scenario);
//		$I->editFormWithExpires($this->paramsForm['name'], $this->paramsFormHasExpires);
//
//		$I = new DisplayFormOnFrontendSteps($scenario);
//		$I->wantTo('Create new article');
//		$I->createNewArticle($this->paramsForm['name'], $this->articlesTitle, $scenario);
//		$I->wantTo('Create new menu items');
//		$I->createNewMenuItem($this->articlesTitle, $this->articles, $this->menuTitle, $this->menuItemType, 'Main Menu');
//		$I->wantTo('Check form display in frontend with has expired');
//		$I->checkFormWithHasExpired($this->menuTitle);
//	}
//
//	/**
//	 * @param DisplayFormOnFrontendSteps $I
//	 * @param                            $scenario
//	 * @throws Exception
//	 */
//	public function checkFormWithExpires(DisplayFormOnFrontendSteps $I, $scenario)
//	{
//		$I->wantTo('Edit form with expires');
//		$I = new AddAFormSteps($scenario);
//		$I->editFormWithExpires($this->paramsForm['name'], $this->paramsFormExpires);
//
//		$I = new DisplayFormOnFrontendSteps($scenario);
//		$I->wantTo('Create new article');
//		$I->createNewArticle($this->paramsForm['name'], $this->articlesTitle, $scenario);
//		$I->wantTo('Create new menu items');
//		$I->createNewMenuItem($this->articlesTitle, $this->articles, $this->menuTitle, $this->menuItemType, 'Main Menu');
//		$I->wantTo('Check form display in frontend with expires');
//		$I->checkFormInFrontend($this->menuTitle, $this->fillForm);
//	}
//
//	/**
//	 * @param DisplayFormOnFrontendSteps $I
//	 * @param                            $scenario
//	 * @throws Exception
//	 */
//	public function clearAll(DisplayFormOnFrontendSteps $I, $scenario)
//	{
//		$I->wantTo('Clear up');
//		$I = new AddAFormSteps($scenario);
//		$I->deleteFormHasSubmitters($this->paramsForm['name']);
//
//		$I = new AddAFieldSteps($scenario);
//		$I->deleteAllField();
//	}
}