<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
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
		$this->params = array(
			'name'  => $this->faker->bothify('Section ?##?'),
			'class' => $this->faker->bothify('section-css ?##?')
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
	 * @param AddASectionSteps $I
	 * @throws Exception
	 */
	public function addSection(AddASectionSteps $I)
	{
		$I->wantToTest('Add a section in redFORM');
		$I->createSection($this->params);
	}
}
