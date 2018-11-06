<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance;

use Page\Acceptance\AddAFieldPage;

class AddAFieldSteps extends Adminredform
{
	/**
	 * Create a field
	 *
	 * @param   array  $params  section fields
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function createField($params)
	{
		$I = $this;
		$I->amOnPage(AddAFieldPage::$URL);
		$I->waitForText(AddAFieldPage::$field, 30, AddAFieldPage::$headPage);
		$I->click(AddAFieldPage::$newButton);
		$I->waitForText(AddAFieldPage::$name, 30, AddAFieldPage::$nameLbl);
		$I->fillField(AddAFieldPage::$nameId, $params['name']);
		$I->waitForText(AddAFieldPage::$fieldType, 30, AddAFieldPage::$fieldTypeLbl);
		$I->selectOptionInChosen(AddAFieldPage::$fieldType, $params['fieldtype']);

		if (isset($params['field_header']))
		{
			$I->waitForText(AddAFieldPage::$fieldHeader, 30, AddAFieldPage::$fieldHeaderLbl);
			$I->fillField(AddAFieldPage::$fieldHeaderId, $params['field_header']);
		}

		if (isset($params['tooltip']))
		{
			$I->waitForText(AddAFieldPage::$tooltip, 30, AddAFieldPage::$tooltipLbl);
			$I->fillField(AddAFieldPage::$tooltipId, $params['tooltip']);
		}

		if (isset($params['default']))
		{
			$I->waitForText(AddAFieldPage::$defaultValue, 30, AddAFieldPage::$defaultValueLbl);
			$I->fillField(AddAFieldPage::$defaultValueId, $params['default']);
		}

		$I->click(AddAFieldPage::$saveButton);
	}
}