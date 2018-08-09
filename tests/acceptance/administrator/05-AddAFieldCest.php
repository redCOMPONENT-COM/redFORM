<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class AddAFieldCest
{
	public function addTextField(\Step\Acceptance\Adminredform $I)
	{
		$I->wantToTest('Add a text field in redFORM');
		$I->doAdministratorLogin();
		$I->createField(
			array(
				'name' => 'Text 1',
				'field_header' => 'Text 1',
				'fieldtype' => 'Textfield',
				'tooltip' => 'a test'
			)
		);
		$I->waitForText('Item saved', 30, ['id' => 'system-message-container']);
		$I->seeElement('//*[@id="fieldList"]//td//*[contains(., "Text 1")]');
	}

	public function addEmailField(\Step\Acceptance\Adminredform $I)
	{
		$I->wantToTest('Add an email field in redFORM');
		$I->doAdministratorLogin();
		$I->createField(
			array(
				'name' => 'Email 1',
				'field_header' => 'Email 1',
				'fieldtype' => 'E-mail',
				'tooltip' => 'a test email field'
			)
		);
		$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
		$I->seeElement('//*[@id="fieldList"]//td//*[contains(., "Email 1")]');
	}
}
