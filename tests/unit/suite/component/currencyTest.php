<?php
/**
 * JLoaderTest
 *
 * @package   Joomla.UnitTest
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters. All rights reserved.
 * @license   GNU General Public License
 */
//require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Autoload.php';

// Register library prefix
JLoader::registerPrefix('RDF', JPATH_LIBRARIES . '/redform');

/**
 * Test class for currency.
 * Generated by PHPUnit on 2009-10-16 at 23:32:06.
 *
 * @package	redFORM.UnitTest
 */
class currencyTest extends JoomlaTestCase
{

	public function getTestGetObjectData()
	{
		return array( 'USD' => array('USD', 840, 'Should return something else'),
		              'AAA' => array('AAA', false, 'Should return false'),
		);
	}

	/**
	 * test get country name function
	 *
	 * @param string $iso
	 * @return void
	 * @dataProvider getTestGetObjectData
	 */
	public function testGetIsoNumber($code, $expect, $message)
	{
		$cur = RedformHelperCurrency::getIsoNumber($code);

		$this->assertEquals(
			$cur,
			$expect,
			$message
		);
	}

	public function getTestGetIsoCodeData()
	{
		return array( 'EUR' => array(978, 'EUR', 'Should return something else'),
		              'EUR string' => array('978', 'EUR', 'Should return something else'),
		              'AAA' => array('AAA', false, 'Should return false'),
		);
	}

	/**
	 * test get country iso code from iso number
	 *
	 * @param int $iso_number
	 * @return void
	 * @dataProvider getTestGetIsoCodeData
	 */
	public function testGetIsoCode($iso_number, $expect, $message)
	{
		$cur = RedformHelperCurrency::getIsoCode($iso_number);

		$this->assertEquals(
			$cur,
			$expect,
			$message
		);
	}
}
