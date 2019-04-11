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
use Step\Acceptance\AddASubmittersSteps as AddASubmittersSteps;

class SubmittersCest
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
			];

		$this->paramsFormExpires = array();
		$this->paramsFormExpires['startDate']   = '2019-04-02';
		$this->paramsFormExpires['endDate']     = '2022-04-02';
		$this->paramsFormExpires['formExpires'] = 'Yes';

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
		$I->createField($this->nameField, 'save&close');
		$I->createField($this->emailField, 'save&close');

		$I = new AddAFormSteps($scenario);
		$I->wantToTest('Create form for check.');
		$I->createForm($this->paramsForm, 'save');
	}

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
	 * @param AddASubmittersSteps $I
	 * @param                     $scenario
	 * @throws Exception
	 */
	public function createSubmitters(AddASubmittersSteps $I, $scenario)
	{
		$I = new AddASubmittersSteps($scenario);
		$I->wantTo('Check create new submitters');
		$I->checkCreateSubmitters($this->paramsForm['name'], $this->fillForm['name'], $this->fillForm['email']);
	}
}