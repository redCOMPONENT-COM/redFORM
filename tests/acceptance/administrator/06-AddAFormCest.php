<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class AddAFormCest
{
	public function addForm(\Step\Acceptance\Adminredform $I)
	{
		$I->wantToTest('Add a form in redFORM');
		$I->doAdministratorLogin();
		$name = "Form 1";
		$I->createForm(
			array(
				'name' => 'Form 1',
				'fields' => ['Text 1', 'Email 1']
			)
		);
		$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
		$I->seeElement('//*[@id="formList"]//td//*[contains(., "' . $name . '")]');
	}
}
