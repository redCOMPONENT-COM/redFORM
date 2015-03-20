<?php
/**
 * @package     Redform.Tests
 * @subpackage  Library
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

// Register library prefix
JLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

/**
 * Test class library helper
 *
 * @package     Redform.Tests
 * @subpackage  Library
 * @since       2.5
 */
class helperTest extends JoomlaTestCase
{
	/**
	 * Data provider
	 *
	 * @return array
	 */
	public function getTestExtractEmailsData()
	{
		return array(
			'nothing' => array('', false, array()),
			'nothing 2' => array('', true, array()),
			'just one' => array('mine@anemail.com', true, array('mine@anemail.com')),
			'just one with , delimiter' => array('mine@anemail.com, ', true, array('mine@anemail.com')),
			'just one with ; delimiter' => array('mine@anemail.com , ', true, array('mine@anemail.com')),
			'just one with ; delimiter' => array('mine@anemail.com; ', true, array('mine@anemail.com')),
			'just one with ; delimiter' => array('mine@anemail.com ; ', true, array('mine@anemail.com')),
			'several with , delimiter' => array('mine@anemail.com , another@anot.the ', true, array('mine@anemail.com', 'another@anot.the')),
			'several with ; delimiter' => array('mine@anemail.com ; another@anot.the ;', true, array('mine@anemail.com', 'another@anot.the')),
			'several with ; delimiter and wrong one' => array('mine@anemail.com ; asds12; another@anot.the ;', true, array('mine@anemail.com', 'another@anot.the')),
		);
	}

	/**
	 * test get extractEmails function
	 *
	 * @param   string  $text      text
	 * @param   bool    $validate  validate emails
	 * @param   mixed   $expected  expected text
	 *
	 * @return void
	 *
	 * @dataProvider getTestExtractEmailsData
	 */
	public function testExtractEmails($text, $validate, $expected)
	{
		$resp = RdfHelper::extractEmails($text, $validate);
		$this->assertTrue(count($expected) == count(array_intersect($expected, $resp)));
	}

	/**
	 * Data provider
	 *
	 * @return array
	 */
	public function getTestIsNonNullDate()
	{
		return array(
			array('dfdgfg', false),
			array('2014-14-03', false),
			array('2014-12-03', true),
			array('2014-12-34', false),
			array('1994-10-03 12:35', true),
			array('1994-10-03 45:35', false),
			array('0000-00-00 00:00:00', false),
			array('0000-00-00 00:00', false),
			array('0000-00-00', false),
			array(null, false),
			array(false, false),
			array(0, false),
			array(123, false),
		);
	}

	/**
	 * test isNonNullDate
	 *
	 * @param   string  $date      date to test
	 * @param   bool    $expected  expected result
	 *
	 * @return void
	 *
	 * @dataProvider getTestIsNonNullDate
	 */
	public function testIsNonNullDate($date, $expected)
	{
		$this->assertEquals(RdfHelper::isNonNullDate($date), $expected);
	}
}
