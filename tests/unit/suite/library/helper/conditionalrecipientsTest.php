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
class conditionalrecipientTest extends JoomlaTestCase
{
	/**
	 * Data provider
	 *
	 * @return array
	 */
	public function getTestGetRecipientsData()
	{
		return array_merge(
			$this->getTestGetRecipientsDataInt(),
			$this->getTestGetRecipientsDataString()
		);
	}

	private function getTestGetRecipientsDataInt()
	{
		$form = new stdClass;
		$form->cond_recipients = <<<TXT
sup@test.com;sup;1;superior;5;
errorsup@test.com;errorsup;1;superior;15;
inf@test.com;inf;1;inferior;15;
errorinf@test.com;errorinf;1;inferior;2;
between@test.com;between;1;between;5;15;
errorbetween@test.com;errorbetween;1;between;15;25;
equal@test.com;equal;1;equal;12;
errorequal@test.com;errorequal;1;equal;13;
TXT;

		// Create a stub for the SomeClass class.
		$answers = $this->getMockBuilder('RdfAnswers')
			->getMock();

		$map = array(
			array(1, array('value' => '12')),
		);

		// Configure the stub.
		$answers->method('getFieldAnswer')
			->will($this->returnValueMap($map));

		return array(
			'int' => array($form, $answers, array(
				array('sup@test.com', 'sup'),
				array('inf@test.com', 'inf'),
				array('between@test.com', 'between'),
				array('equal@test.com', 'equal'),
			))
		);
	}

	private function getTestGetRecipientsDataString()
	{
		$form = new stdClass;
		$form->cond_recipients = <<<TXT
sup@test.com;sup;1;superior;abc;
errorsup@test.com;errorsup;1;superior;jkl;
inf@test.com;inf;1;inferior;jkl;
errorinf@test.com;errorinf;1;inferior;abc;
between@test.com;between;1;between;abc;jkl;
errorbetween@test.com;errorbetween;1;between;jkl;tuv;
equal@test.com;equal;1;equal;defi;
errorequal@test.com;errorequal;1;equal;defis;
regex@test.com;regex;1;regex;/d[a-z]+i/;
errorregex@test.com;errorregex;1;regex;/d[0-9]+i/;
TXT;

		// Create a stub for the SomeClass class.
		$answers = $this->getMockBuilder('RdfAnswers')
			->getMock();

		$map = array(
			array(1, array('value' => 'defi'))
		);

		// Configure the stub.
		$answers->method('getFieldAnswer')
			->will($this->returnValueMap($map));

		return array(
			'string' => array($form, $answers, array(
				array('sup@test.com', 'sup'),
				array('inf@test.com', 'inf'),
				array('between@test.com', 'between'),
				array('equal@test.com', 'equal'),
				array('regex@test.com', 'regex'),
			))
		);
	}

	/**
	 * test get getRecipients function
	 *
	 * @param   object  $form      form data
	 * @param   object  $answers   answers
	 * @param   mixed   $expected  expected recipients
	 *
	 * @return void
	 *
	 * @dataProvider getTestGetRecipientsData
	 */
	public function testGetRecipients($form, $answers, $expected)
	{
		$recipients = RdfHelperConditionalrecipients::getRecipients($form, $answers);
		$this->assertEquals($recipients, $expected);
	}
}
