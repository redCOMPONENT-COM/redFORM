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

		$this->paramsForm = array(
			'name' => $this->faker->bothify('Form ?##?'),
			'fields_1' => $this->paramsTextField1['name'],
			'section_1' => $this->paramsSection1['name'],
			'fields_2' => $this->paramsTextField2['name'],
			'section_2' => $this->paramsSection2['name']
		);
	}
	/**
	 * @param AcceptanceTester $I
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

		$I = new AddAFieldSteps($scenario);
		$I->createField($this->paramsTextField2, 'save&close');

		$I = new AddASectionSteps($scenario);
		$I->createSection($this->paramsSection1);

		$I = new AddASectionSteps($scenario);
		$I->createSection($this->paramsSection2);

		$I = new AddAFormSteps($scenario);
		$I->createForm($this->paramsForm);
	}
}
