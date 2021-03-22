<?php
/**
 * @package     redFORM
 * @subpackage  Cest
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Acceptance\AddAFormSteps as AddAFormSteps;
use Step\Acceptance\AddAFieldSteps as AddAFieldSteps;
use Step\Acceptance\AddASectionSteps as AddASectionSteps;

/**
 * Class FormCest
 * @since 3.3.28
 */
class FormCest
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
	protected $paramsTextField1;

	/**
	 * @var array
	 * @since 3.3.28
	 */
	protected $paramsTextField2;

	/**
	 * @var array
	 * @since 3.3.28
	 */
	protected $paramsSection1;

	/**
	 * @var array
	 * @since 3.3.28
	 */
	protected $paramsSection2;

	/**
	 * @var array
	 * @since 3.3.28
	 */
	protected $paramsFormSave;

	/**
	 * @var array
	 * @since 3.3.28
	 */
	protected $paramsFormSaveClose;

	/**
	 * @var array
	 * @since 3.3.28
	 */
	protected $paramsFormMissingName;

	/**
	 * @var string
	 * @since 3.3.28
	 */
	protected $paramsFormSaveEdit;

	/**
	 * @var string
	 * @since 3.3.28
	 */
	protected $paramsFormSaveCloseEdit;

	/**
	 * FormCest constructor.
	 * @since 3.3.28
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
			'status_1'  => "Publish",
			'fields_2' => $this->paramsTextField2['name'],
			'section_2' => $this->paramsSection2['name'],
			'required' => 'Yes',
			'status_2'  => "Unpublish",
		);

		$this->paramsFormMissingName = array();
		$this->paramsFormMissingName['startDate']   = '2019-04-02';
		$this->paramsFormMissingName['endDate']     = '2020-04-02';
		$this->paramsFormMissingName['formExpires'] = 'Yes';

		$this->paramsFormSaveEdit = $this->faker->bothify('Edit FormSave ?##?');
		$this->paramsFormSaveCloseEdit = $this->faker->bothify('Edit FormSaveClose ?##?');
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
		$i->wantToTest('Add forms in redFORM');

		$i = new AddAFieldSteps($scenario);
		$i->createField($this->paramsTextField1, 'save&close');
		$i->createField($this->paramsTextField2, 'save&close');

		$i = new AddASectionSteps($scenario);
		$i->createSection($this->paramsSection1, 'save&close');
		$i->createSection($this->paramsSection2, 'save&close');

		$i = new AddAFormSteps($scenario);
		$i->createForm($this->paramsFormSave, 'save');
		$i->createForm($this->paramsFormSaveClose, 'save&close');
	}

	/**
	 * @param AddAFormSteps $i
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function changeStatusFieldOnForm(AddAFormSteps $i)
	{
		$i->wantToTest('Change Status Field on Form');
		$i->changeStatusFormField($this->paramsFormSaveClose);
		$i->wantToTest('Check Status Field on Form');
		$i->selectStatusFormField($this->paramsFormSaveClose);
	}

	/**
	 * @param AddAFieldSteps $i
	 * @param                $scenario
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function createFormMissingName(AddAFieldSteps $i, $scenario)
	{
		$i->wantToTest('Add form in redFORM with missing name');
		$i = new AddAFormSteps($scenario);
		$i->createFormMissingName($this->paramsFormMissingName);
	}

	/**
	 * @param AddAFormSteps $i
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function editForm(AddAFormSteps $i)
	{
		$i->wantToTest('Edit forms in redFORM');
		$i->editForm($this->paramsFormSave['name'], $this->paramsFormSaveEdit, 'save');
		$i->editForm($this->paramsFormSaveClose['name'], $this->paramsFormSaveCloseEdit, 'save&close');
	}

	/**
	 * @param AddAFormSteps $i
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function publishForm(AddAFormSteps $i)
	{
		$i->wantToTest('Publish forms in redFORM');
		$i->publishForm($this->paramsFormSaveEdit);
		$i->publishForm($this->paramsFormSaveCloseEdit);
	}

	/**
	 * @param AddAFormSteps $i
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function unpublishForm(AddAFormSteps $i)
	{
		$i->wantToTest('Unpublish forms in redFORM');
		$i->unpublishForm($this->paramsFormSaveEdit);
		$i->unpublishForm($this->paramsFormSaveCloseEdit);
	}

	/**
	 * @param AddAFieldSteps $i
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function deleteFieldUsedInForm(AddAFieldSteps $i)
	{
		$i->wantToTest('Delete field used in form');
		$i->deleteFieldUsedInForm($this->paramsTextField1['name']);
		$i->deleteFieldUsedInForm($this->paramsTextField2['name']);
	}
	/**
	 * @param AddAFormSteps $i
	 * @throws Exception
	 * @since 3.3.28
	 */
	public function deleteForm(AddAFormSteps $i)
	{
		$i->wantToTest('Delete forms in redFORM');
		$i->deleteForm($this->paramsFormSaveEdit);
		$i->deleteForm($this->paramsFormSaveCloseEdit);
	}
}
