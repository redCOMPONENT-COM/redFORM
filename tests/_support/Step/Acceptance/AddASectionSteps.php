<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance;

use Page\Acceptance\AddASectionPage as AddASectionPage;

class AddASectionSteps extends Adminredform
{
	/**
	 * Create a section
	 *
	 * @param   array  $params  section fields
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function createSection($params)
	{
		$I = $this;
		$I->amOnPage(AddASectionPage::$URL);
		$I->waitForText(AddASectionPage::$section, 30, AddASectionPage::$headPage);
		$I->click(AddASectionPage::$newButton);
		$I->waitForText(AddASectionPage::$name, 30, AddASectionPage::$nameLbl);
		$I->fillField(AddASectionPage::$nameId, $params['name']);

		if (!empty($params['class']))
		{
			$I->waitForText(AddASectionPage::$class, 30, AddASectionPage::$classLbl);
			$I->fillField(AddASectionPage::$classId, $params['class']);
		}

		$I->click(AddASectionPage::$saveButton);
		$I->waitForText(AddASectionPage::$saveItem, 30, AddASectionPage::$messageSuccess);
		$I->see(AddASectionPage::$saveItem);
	}


}