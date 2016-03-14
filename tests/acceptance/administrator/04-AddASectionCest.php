<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class AddASectionCest
{
	public function addSection(\Step\Acceptance\Adminredform $I)
	{
		$I->wantToTest('Add a section in redFORM');
		$I->doAdministratorLogin();
		$I->createSection(
			array(
				'name' => 'Section 1',
				'class' => 'section-css',
				'description' => '<p>The description goes here</p>'
			)
		);
		$I->waitForText('Item successfully saved', 30, ['id' => 'system-message-container']);
		$I->seeElement('//*[@id="sectionList"]//td//*[contains(., "Section 1")]');
	}
}
