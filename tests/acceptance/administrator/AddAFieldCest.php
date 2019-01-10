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

		$this->paramsEmailField = array(
			'name' => $this->faker->bothify('Email ?##?'),
			'field_header' => $this->faker->bothify('Email ?##?'),
			'fieldtype' => 'E-mail',
			'tooltip' => $this->faker->bothify('Test mail #####')
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
	 * @param AddAFieldSteps $I
	 * @throws Exception
	 */
	public function addField(AddAFieldSteps $I)
	{
		$I->wantToTest('Add fields in redFORM');
		$I->createField($this->paramsTextField);
		$I->createField($this->paramsEmailField);
	}
}
