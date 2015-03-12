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
JLoader::registerPrefix('Plghs', JPATH_SITE . '/plugins/redform/helpscout/lib');

/**
 * Test class for tagsreplace.
 *
 * @package     Redform.Tests
 * @subpackage  Library
 * @since       2.5
 */
class formTest extends JoomlaTestCase
{
	/**
	 * provides test xml
	 *
	 * @return string
	 */
	private function getBaseXml()
	{
		$xml = <<<XML
	<form>
		<id>1</id>
		<mailboxId>41175</mailboxId>
		<emailFieldId>3</emailFieldId>
		<subjectFieldId>1</subjectFieldId>
		<bodyFieldId>13</bodyFieldId>
		<tags>
			<tag>tag1</tag>
			<tag>tag2</tag>
		</tags>
		<ccList>
			<email>julv@free.fr</email>
			<email>julv2@free.fr</email>
		</ccList>
		<bccList>
			<email>julv3@free.fr</email>
			<email>julv4@free.fr</email>
		</bccList>
		<assignTo>65300</assignTo>
	</form>
XML;

		return $xml;
	}

	/**
	 * Data provider
	 *
	 * @return array
	 */
	public function getTestGetMailboxIdData()
	{
		$xml = $this->getBaseXml();

		return array(
			'normal' => array($xml, 41175)
		);
	}

	/**
	 * test get extractEmails function
	 *
	 * @param   string  $xml       xml
	 * @param   int     $expected  expected id
	 *
	 * @return void
	 *
	 * @dataProvider getTestGetMailboxIdData
	 */
	public function testGetMailboxId($xml, $expected)
	{
		$element = new SimpleXMLElement($xml);
		$config = new PlghsConfigForm($element);
		$this->assertEquals($config->getMailboxId(), $expected);
	}

	/**
	 * Data provider
	 *
	 * @return array
	 */
	public function getTestGetTagsData()
	{
		$xml = $this->getBaseXml();

		return array(
			'normal' => array($xml, array('tag1', 'tag2'))
		);
	}

	/**
	 * test get getTestGetTags function
	 *
	 * @param   string  $xml       xml
	 * @param   int     $expected  expected value
	 *
	 * @return void
	 *
	 * @dataProvider getTestGetTagsData
	 */
	public function testGetTags($xml, $expected)
	{
		$element = new SimpleXMLElement($xml);
		$config = new PlghsConfigForm($element);
		$this->assertEquals($config->getTags(), $expected);
	}
}
