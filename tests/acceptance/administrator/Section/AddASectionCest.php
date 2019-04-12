<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Step\Acceptance\AddASectionSteps as AddASectionSteps;
class AddASectionCest
{
	/**
	 * AddASectionCest constructor.
	 */
	public function __construct()
	{
		$this->faker   = Faker\Factory::create();
		$this->sectionSave = array(
			'name'  => $this->faker->bothify('Section ?##?'),
			'class' => $this->faker->bothify('section-css ?##?')
		);

		$this->sectionSaveClose = array(
			'name'  => $this->faker->bothify('Section save close ?##?'),
			'class' => $this->faker->bothify('section-css save close ?##?')
		);

		$this->sectionEdit = array(
			'name'  => $this->faker->bothify('Section edit ?##?'),
			'class' => $this->faker->bothify('section-css edit ?##?')
		);

		$this->sectionEditSecond = array(
			'name'  => $this->faker->bothify('Section edit second ?##?'),
			'class' => $this->faker->bothify('section-css edit second ?##?')
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
	 * @param AddASectionSteps $I
	 * @throws Exception
	 */
	public function addSection(AddASectionSteps $I)
	{
		$I->wantToTest('Add a section in redFORM');
		$I->createSection($this->sectionSave, 'save');
		$I->createSection($this->sectionSaveClose, 'save&close');
		$I->createSection($this->sectionSave, 'cancel');
	}

	/**
	 * @param AddASectionSteps $I
	 * @throws Exception
	 */
	public function editSection(AddASectionSteps $I)
	{
		$I->wantToTest('Edit a section in redFORM');
		$I->editSection($this->sectionSave['name'], $this->sectionEdit,'save');
		$I->editSection($this->sectionSaveClose['name'], $this->sectionEditSecond ,'save&close');
	}

	/**
	 * @param AddASectionSteps $I
	 * @throws Exception
	 */
	public function deleteSection(AddASectionSteps $I)
	{
		$I->wantToTest('Delete a section in redFORM');
		$I->deleteSection($this->sectionEdit['name']);
		$I->deleteSection($this->sectionEditSecond['name']);
	}
}
