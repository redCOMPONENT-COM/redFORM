<?php
/**
 * @package     redFORM
 * @subpackage  Cest AddAField
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Acceptance\AddAFieldSteps as AddAFieldSteps;

class AddAFieldCest
{
	/**
	 * @var \Faker\Generator
	 * @since 3.3.27
	 */
	protected $faker;

	/**
	 * @var array
	 * @since 3.3.27
	 */
	protected $paramsTextField;

	/**
	 * @var array
	 * @since 3.3.27
	 */
	protected $paramsTextFieldSaveClose;

	/**
	 * @var array
	 * @since 3.3.27
	 */
	protected $paramsTextFieldEdit;

	/**
	 * @var array
	 * @since 3.3.27
	 */
	protected $paramsEmailField;

	/**
	 * @var array
	 * @since 3.3.27
	 */
	protected $paramsEmailFieldEdit;

	/**
	 * AddAFieldCest constructor.
	 */
	public function __construct()
	{
		$this->faker   = Faker\Factory::create();
		$this->paramsTextField = array(
			'name' => $this->faker->bothify('Text name ?##?'),
			'field_header' => $this->faker->bothify('Text field_header ?##?'),
			'fieldtype' => 'Date',
			'tooltip' => $this->faker->bothify('Test text tooltip #####')
		);

		$this->paramsTextFieldSaveClose = array(
			'name' => $this->faker->bothify('Text name save close ?##?'),
			'field_header' => $this->faker->bothify('Text field_header save close ?##?'),
			'fieldtype' => 'Date',
			'tooltip' => $this->faker->bothify('Test text tooltip save close #####')
		);

		$this->paramsTextFieldEdit = array(
			'name' => $this->faker->bothify('Text name edit ?##?'),
			'field_header' => $this->faker->bothify('Text field_header edit ?##?'),
			'fieldtype' => 'Date',
			'tooltip' => $this->faker->bothify('Test text tooltip edit #####')
		);

		$this->paramsEmailField = array(
			'name' => $this->faker->bothify('Email name ?##?'),
			'field_header' => $this->faker->bothify('Email field_header ?##?'),
			'fieldtype' => 'E-mail',
			'tooltip' => $this->faker->bothify('Test email tooltip #####')
		);

		$this->paramsEmailFieldEdit = array(
			'name' => $this->faker->bothify('Email name edit ?##?'),
			'field_header' => $this->faker->bothify('Email field_header edit ?##?'),
			'fieldtype' => 'E-mail',
			'tooltip' => $this->faker->bothify('Test email tooltip edit #####')
		);
	}
	/**
	 * @param AcceptanceTester $I
	 * @throws Exception
	 */
	public function _before(AcceptanceTester $I)
	{
		$I->doAdministratorLogin("admin", "admin", null);
	}
	/**
	 * @param AddAFieldSteps $I
	 * @throws Exception
	 */
	public function addField(AddAFieldSteps $I)
	{
		$I->wantToTest('Add fields in redFORM');
		$I->createField($this->paramsTextField, 'save');
		$I->createField($this->paramsTextFieldSaveClose, 'save&close');
		$I->createField($this->paramsEmailField, 'save&new');
		$I->createField($this->paramsEmailField, 'cancel');
	}

	/**
	 * @param AddAFieldSteps $I
	 * @throws Exception
	 */
	public function addFieldMissingName(AddAFieldSteps $I)
	{
		$I->wantToTest('Add fields in redFORM with missing name');
		$I->createFieldMissingName($this->paramsTextField);
	}

	/**
	 * @param AddAFieldSteps $I
	 * @throws Exception
	 */
	public function editField(AddAFieldSteps $I)
	{
		$I->wantToTest('Edit fields in redFORM');
		$I->editField($this->paramsTextField['name'], $this->paramsTextFieldEdit, 'save');
		$I->editField($this->paramsEmailField['name'], $this->paramsEmailFieldEdit, 'save&close');
	}

	/**
	 * @param AddAFieldSteps $I
	 * @throws Exception
	 */
	public function copyField(AddAFieldSteps $I)
	{
		$I->wantToTest('Copy fields in redFORM');
		$I->copyField($this->paramsTextFieldEdit['name']);
	}

	/**
	 * @param AddAFieldSteps $I
	 * @throws Exception
	 */
	public function deleteField(AddAFieldSteps $I)
	{
		$I->wantToTest('Delete fields in redFORM');
		$I->deleteField('Copy of '.$this->paramsTextFieldEdit['name']);
		$I->deleteField($this->paramsTextFieldEdit['name']);
		$I->deleteField($this->paramsTextFieldSaveClose['name']);
		$I->deleteField($this->paramsEmailFieldEdit['name']);
	}
}
