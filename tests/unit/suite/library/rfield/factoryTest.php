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
JLoader::registerPrefix('Redform', JPATH_LIBRARIES . '/redform');

/**
 * Test class library rfield factory
 *
 * @package     Redform.Tests
 * @subpackage  Library
 * @since       2.5
 */
class RfieldFactoryTest extends JoomlaTestCase
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
		$types = RedformRfieldFactory::getTypes();

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
		foreach ($types = RedformRfieldFactory::getTypes() as $type)
		{
			$instance = RedformRfieldFactory::getFieldType($type);
			$this->assertInstanceOf('RedformRfield' . ucfirst($type), $instance);
		}
	}

	/**
	 * Test getField
	 *
	 * @return void
	 */
	public function testGetField()
	{
		$id = '123';
		$type = 'textfield';

		$class = $this->getMockClass(
			'RedformRfieldFactory',          /* name of class to mock     */
			array('getType') /* list of methods to mock   */
		);

		$class::staticExpects($this->once())
			->method('getType')
			->will($this->returnValue('textfield'));

		$this->assertInstanceOf('RedformRfield' . ucfirst($type), $class::getField($id));
	}
}
