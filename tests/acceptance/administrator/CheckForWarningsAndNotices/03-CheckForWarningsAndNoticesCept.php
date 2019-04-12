<?php
/**
 * @package     redCORE
 * @subpackage  Cept
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Load the Step Object Page
$I = new \AcceptanceTester($scenario);
$I->wantToTest(' that there are no Warnings or Notices in redFORM');
$I->doAdministratorLogin();
$I->checkForPhpNoticesOrWarnings('administrator/index.php?option=com_redform');
$I->checkForPhpNoticesOrWarnings('administrator/index.php?option=com_redform&view=fields');
$I->checkForPhpNoticesOrWarnings('administrator/index.php?option=com_redform&view=forms');
$I->checkForPhpNoticesOrWarnings('administrator/index.php?option=com_redform&view=logs');
$I->checkForPhpNoticesOrWarnings('administrator/index.php?option=com_redform&view=payments');
$I->checkForPhpNoticesOrWarnings('administrator/index.php?option=com_redform&view=sections');
$I->checkForPhpNoticesOrWarnings('administrator/index.php?option=com_redform&view=submitters');
