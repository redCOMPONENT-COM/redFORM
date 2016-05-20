<?php
/**
* @package     redCORE
* @subpackage  Cept
* @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
* @license     GNU General Public License version 2 or later; see LICENSE.txt
*/

$I = new AcceptanceTester($scenario);
$I->wantTo('Uninstall redFORM Extension');
$I->doAdministratorLogin();
$I->amOnPage('/administrator/index.php?option=com_installer&view=manage');
$I->waitForText('Extensions: Manage', 30, ['css' => 'H1']);
$I->fillField('#filter_search', 'redFORM - Component');
$I->click(['xpath' => "//button[@type='submit' and @data-original-title='Search']"]);
$I->waitForElement(['id' => 'manageList']);
$I->click(['xpath' => "//input[@id='cb0']"]);
$I->click(['xpath' => "//div[@id='toolbar-delete']/button"]);
$I->acceptPopup();
$I->waitForText('Uninstalling the component was successful', 30, ['id' => 'system-message-container']);
$I->see('Uninstalling the component was successful', ['id' => 'system-message-container']);
$I->fillField(['id' => 'filter_search'], 'redFORM - component');
$I->click(['xpath' => "//button[@type='submit' and @data-original-title='Search']"]);
$I->waitForText('There are no extensions installed matching your query.', 30, ['class' => 'alert-no-items']);
$I->see('There are no extensions installed matching your query.', ['class' => 'alert-no-items']);
