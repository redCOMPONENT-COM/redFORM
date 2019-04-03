<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Acceptance\AddAFormSteps as AddAFormSteps;
use Step\Acceptance\AddAFieldSteps as AddAFieldSteps;
use Step\Acceptance\AddASectionSteps as AddASectionSteps;
class AddAFormCest
{
	/**
	 * AddAFormCest constructor.
	 */
	public function __construct()
	{
		$this->faker   = Faker\Factory::create();
		$this->paramsTextField1 = array(
			'name' => $this->faker->bothify('Text ?##?'),
			'field_header' => $this->faker->bothify('Text ?##?'),
			'fieldtype' => 'Checkbox',
			'tooltip' => $this->faker->bothify('Test text #####')
		);

		$this->paramsTextField2 = array(
			'name' => $this->faker->bothify('Text ?##?'),
			'field_header' => $this->faker->bothify('Text ?##?'),
			'fieldtype' => 'Radio',
			'tooltip' => $this->faker->bothify('Test text #####')
		);

		$this->paramsSection1 = array(
			'name'  => $this->faker->bothify('Section ?##?'),
			'class' => $this->faker->bothify('section-css ?##?')
		);
		$this->paramsSection2 = array(
			'name'  => $this->faker->bothify('Section ?##?'),
			'class' => $this->faker->bothify('section-css ?##?')
		);

		$this->paramsFormSave = array(
			'name' => $this->faker->bothify('FormSave ?##?'),
			'fields_1' => $this->paramsTextField1['name'],
			'section_1' => $this->paramsSection1['name'],
			'fields_2' => $this->paramsTextField2['name'],
			'section_2' => $this->paramsSection2['name'],
			'required' => 'Yes'
		);

		$this->paramsFormSaveClose = array(
			'name' => $this->faker->bothify('FormSaveClose ?##?'),
			'fields_1' => $this->paramsTextField1['name'],
			'section_1' => $this->paramsSection1['name'],
			'fields_2' => $this->paramsTextField2['name'],
			'section_2' => $this->paramsSection2['name'],
			'required' => 'Yes'
		);

		$this->paramsFormMissingName = array();
		$this->paramsFormMissingName['startDate']   = '2019-04-02';
		$this->paramsFormMissingName['endDate']	    = '2020-04-02';
		$this->paramsFormMissingName['formExpires']	= 'Yes';

		$this->paramsFormSaveEdit = $this->faker->bothify('Edit FormSave ?##?');
		$this->paramsFormSaveCloseEdit = $this->faker->bothify('Edit FormSaveClose ?##?');
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
		$I->wantToTest('Add forms in redFORM');

		$I = new AddAFieldSteps($scenario);
		$I->createField($this->paramsTextField1, 'save&close');
		$I->createField($this->paramsTextField2, 'save&close');

		$I = new AddASectionSteps($scenario);
		$I->createSection($this->paramsSection1, 'save&close');
		$I->createSection($this->paramsSection2, 'save&close');

		$I = new AddAFormSteps($scenario);
		$I->createForm($this->paramsFormSave, 'save');
		$I->createForm($this->paramsFormSaveClose, 'save&close');

	}

	/**
	 * @param AddAFieldSteps $I
	 * @param                $scenario
	 * @throws Exception
	 */
	public function createFormMissingName(AddAFieldSteps $I, $scenario)
	{
		$I->wantToTest('Add form in redFORM with missing name');
		$I = new AddAFormSteps($scenario);
		$I->createFormMissingName($this->paramsFormMissingName);
	}

	/**
	 * @param AddAFormSteps $I
	 * @throws Exception
	 */
	public function editForm(AddAFormSteps $I)
	{
		$I->wantToTest('Edit forms in redFORM');
		$I->editForm($this->paramsFormSave['name'], $this->paramsFormSaveEdit, 'save');
		$I->editForm($this->paramsFormSaveClose['name'], $this->paramsFormSaveCloseEdit, 'save&close');
	}

	/**
	 * @param AddAFormSteps $I
	 * @throws Exception
	 */
	public function publishForm(AddAFormSteps $I)
	{
		$I->wantToTest('Publish forms in redFORM');
		$I->publishForm($this->paramsFormSaveEdit);
		$I->publishForm($this->paramsFormSaveCloseEdit);
	}

	/**
	 * @param AddAFormSteps $I
	 * @throws Exception
	 */
	public function unpublishForm(AddAFormSteps $I)
	{
		$I->wantToTest('Unpublish forms in redFORM');
		$I->unpublishForm($this->paramsFormSaveEdit);
		$I->unpublishForm($this->paramsFormSaveCloseEdit);
	}

	/**
	 * @param AddAFormSteps $I
	 * @throws Exception
	 */
	public function deleteForm(AddAFormSteps $I)
	{
		$I->wantToTest('Delete forms in redFORM');
		$I->deleteForm($this->paramsFormSaveEdit);
		$I->deleteForm($this->paramsFormSaveCloseEdit);
	}
}
