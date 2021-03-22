<?php
/**
 * @package     redFORM
 * @subpackage  Cest
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Acceptance\AddAFieldSteps;
use Step\Acceptance\AddAFormSteps;
use Step\Acceptance\DisplayFormOnFrontendSteps;

/**
 * Class DisplayMessageNotificationFormCest
 * @since 3.3.28
 */
class DisplayMessageNotificationFormCest
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
	protected $telephoneField;

	/**
	 * @var array
	 * @since 3.3.28
	 */
	protected $noteField;

	/**
	 * @var array
	 * @since 3.3.28
	 */
	protected $paramsForm;

	/**
	 * @var array
	 * @since 3.3.28
	 */
	protected $telephoneForm;

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
	 * DisplayMessageNotificationFormCest constructor.
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
				'required'          => 'Yes',
				'displayNotification' => 'yes',
				'notificationMessage' => 'Thanks for your submission. We will contact soon.',
			];

		$this->telephoneForm =
			[
				'fields'            => $this->telephoneField['name'],
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
				'name'                => $this->faker->bothify('Name ?##?'),
				'email'               => $this->faker->email,
				'telephone'           => $this->faker->phoneNumber,
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
	 * @param               $scenario
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function createForm(AddAFormSteps $i, $scenario)
	{
		$i = new AddAFieldSteps($scenario);
		$i->createField($this->nameField, 'save&close');
		$i->createField($this->emailField, 'save&close');
		$i->createField($this->telephoneField, 'save&close');

		$i = new AddAFormSteps($scenario);
		$i->wantToTest('Create form for check.');
		$i->createForm($this->paramsForm, 'save');
		$i->editAndAddFieldForForm($this->paramsForm['name'], $this->telephoneForm);
	}

	/**
	 * @param DisplayFormOnFrontendSteps $i
	 * @param                            $scenario
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function checkDisplayForm(DisplayFormOnFrontendSteps $i, $scenario)
	{
		$i = new DisplayFormOnFrontendSteps($scenario);
		$i->wantTo('Create new article');
		$i->createNewArticle($this->paramsForm['name'], $this->articlesTitle, $scenario);
		$i->wantTo('Create new menu items');
		$i->createNewMenuItem($this->articlesTitle, $this->articles, $this->menuTitle, $this->menuItemType, 'Main Menu');
		$i->wantTo('Check form display in frontend');
		$i->checkFormWithNotificationInFrontend($this->menuTitle, $this->paramsForm['notificationMessage'], $this->fillForm);
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
