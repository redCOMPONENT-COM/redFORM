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
JLoader::registerPrefix('R', JPATH_LIBRARIES . '/redcore');

/**
 * Test class library rfield factory
 *
 * @package     Redform.Tests
 * @subpackage  Library
 * @since       2.5
 */
class RfieldFactoryTest extends TestCase
{
	private $fixedTypes = array(
		'checkbox',
		'date',
		'email',
		'fileupload',
		'fullname',
		'hidden',
		'info',
		'multiselect',
		'price',
		'radio',
		'recipients',
		'select',
		'textarea',
		'textfield',
		'username',
		'wysiwyg',
	);

	/**
	 * test get fixed types
	 *
	 * @return void
	 */
	public function testGetFixedTypes()
	{
		$types = RdfRfieldFactory::getTypes();

		$intersect = array_intersect($types, $this->fixedTypes);
		$this->assertTrue(count($intersect) == count($this->fixedTypes));
	}

	/**
	 * test get fixed types instances
	 *
	 * @return void
	 */
	public function testGetFixedTypesInstances()
	{
		foreach ($types = RdfRfieldFactory::getTypes() as $type)
		{
			$instance = RdfRfieldFactory::getFieldType($type);
			$this->assertInstanceOf('RdfRfield' . ucfirst($type), $instance);
		}
	}
}
