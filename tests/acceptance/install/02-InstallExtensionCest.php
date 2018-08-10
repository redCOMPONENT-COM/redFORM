<?php
	
	/**
	 * Class InstallExtensionCest
	 *
	 * @package  AcceptanceTester
	 *
	 * @link     http://codeception.com/docs/07-AdvancedUsage
	 *
	 * @since    2.1
	 */
class InstallExtensionCest
{
	public function install(\AcceptanceTester $I)
	{
		$I->wantToTest('redFORM installation in Joomla 3');
		$I->doAdministratorLogin();
		
		$path = $I->getConfig('redform packages url') . 'redform.zip';
		$I->wantToTest('Path for get redform.zip');
		$I->wantToTest($path);
		$I->comment($path);
		$I->installExtensionFromUrl($path . 'redform.zip');
	}
}
