<?php
/**
 * @package     Redform.Tests
 * @subpackage  Library
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

require_once 'PHPUnit/Autoload.php';

// Register library prefix
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

/**
 * Test class for tagsreplace.
 *
 * @package     Redform.Tests
 * @subpackage  Library
 * @since       2.5
 */
class tagsreplaceTest extends JoomlaTestCase
{
	/**
	 * Data provider
	 *
	 * @return array
	 */
	public function getTestReplaceData()
	{
		$formdata = new stdclass;
		$formdata->formname = 'test form';

		$name = new RdfRfieldFullname;
		$name->setId(2);
		$name->setValue('julien');
		$name->setData(array('field' => 'text'));

		$email = new RdfRfieldEmail;
		$email->setId(3);
		$email->setValue('julien@redweb.dk');
		$email->setData(array('field' => 'email'));

		$answers = new RdfAnswers;
		$answers->addField($name);
		$answers->addField($email);

		return array(
			'nothing' => array($formdata, $answers, 'a first test', 'a first test'),
			'[formname]' => array($formdata, $answers, 'a first test with [formname]', 'a first test with ' . $formdata->formname),
			'[[form name]]' => array($formdata, $answers, 'a first test with [[formname]]', 'a first test with [' . $formdata->formname . ']'),
			'unknown answer' => array($formdata, $answers, 'a first test with field [answer_24]', 'a first test with field [answer_24]'),
			'known answer' => array($formdata, $answers, 'a first test with field [answer_2]', 'a first test with field julien'),
			'2 answers' => array($formdata, $answers, 'a first test with field [answer_2], and [answer_3]', 'a first test with field julien, and julien@redweb.dk'),
		);
	}

	/**
	 * test get country name function
	 *
	 * @param   object  $formdata  form data
	 * @param   object  $answers   answers
	 * @param   string  $text      text
	 * @param   string  $expected  expected text
	 *
	 * @return void
	 *
	 * @dataProvider getTestReplaceData
	 */
	public function testReplace($formdata, $answers, $text, $expected)
	{
		$helper = new RdfHelperTagsreplace($formdata, $answers);

		$resp = $helper->replace($text);

		$this->assertEquals($resp, $expected);
	}
}
