<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Step\Acceptance\AddAFieldSteps as AddAFieldSteps;
class AddAFieldCest
{
	/**
	 * AddAFieldCest constructor.
	 */
	public function __construct()
	{
		$this->faker   = Faker\Factory::create();
		$this->paramsTextField = array(
			'name' => $this->faker->bothify('Text ?##?'),
			'field_header' => $this->faker->bothify('Text ?##?'),
			'fieldtype' => 'Date',
			'tooltip' => $this->faker->bothify('Test text #####')
		);

		$this->paramsTextFieldSaveClose = array(
			'name' => $this->faker->bothify('Text save close ?##?'),
			'field_header' => $this->faker->bothify('Text save close ?##?'),
			'fieldtype' => 'Date',
			'tooltip' => $this->faker->bothify('Test text save close #####')
		);

		$this->paramsTextFieldEdit = array(
			'name' => $this->faker->bothify('Edit Text ?##?'),
			'field_header' => $this->faker->bothify('Edit Text ?##?'),
			'fieldtype' => 'Date',
			'tooltip' => $this->faker->bothify('Edit Test text #####')
		);

		$this->paramsEmailField = array(
			'name' => $this->faker->bothify('Email ?##?'),
			'field_header' => $this->faker->bothify('Email ?##?'),
			'fieldtype' => 'E-mail',
			'tooltip' => $this->faker->bothify('Test mail #####')
		);

		$this->paramsEmailFieldEdit = array(
			'name' => $this->faker->bothify('Edit Email ?##?'),
			'field_header' => $this->faker->bothify('Edit Email ?##?'),
			'fieldtype' => 'E-mail',
			'tooltip' => $this->faker->bothify('Edit Test mail #####')
		);
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
	 * @param AddAFieldSteps $I
	 * @throws Exception
	 */
	public function addField(AddAFieldSteps $I)
	{
		$I->wantToTest('Add fields in redform');
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
		$I->wantToTest('Add fields in redform with missing name');
		$I->createFieldMissingName($this->paramsTextField);
	}

	/**
	 * @param AddAFieldSteps $I
	 * @throws Exception
	 */
	public function editField(AddAFieldSteps $I)
	{
		$I->wantToTest('Edit fields in redform');
		$I->editField($this->paramsTextField['name'], $this->paramsTextFieldEdit, 'save');
		$I->editField($this->paramsEmailField['name'], $this->paramsEmailFieldEdit, 'save&close');
	}

	/**
	 * @param AddAFieldSteps $I
	 * @throws Exception
	 */
	public function copyField(AddAFieldSteps $I)
	{
		$I->wantToTest('Copy fields in redform');
		$I->copyField($this->paramsTextFieldEdit['name']);
	}

	/**
	 * @param AddAFieldSteps $I
	 * @throws Exception
	 */
	public function deleteField(AddAFieldSteps $I)
	{
		$I->wantToTest('Delete fields in redform');
		$I->deleteField('Copy of '.$this->paramsTextFieldEdit['name']);
		$I->deleteField($this->paramsTextFieldEdit['name']);
		$I->deleteField($this->paramsTextFieldSaveClose['name']);
		$I->deleteField($this->paramsEmailFieldEdit['name']);
	}
}
