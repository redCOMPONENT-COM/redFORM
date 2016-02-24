<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Rfield
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * redFORM field factory
 *
 * @package     Redform.Libraries
 * @subpackage  Rfield
 * @since       2.5
 */
abstract class RdfRfieldFactory extends JObject
{
	protected static $fields;

	/**
	 * Return all supported types
	 *
	 * @return array
	 */
	public static function getTypes()
	{
		jimport('joomla.filesystem.folder');

		$xmlFiles = JFolder::files(__DIR__, '.xml');

		$types = array();

		foreach ($xmlFiles as $f)
		{
			$types[] = substr($f, 0, -4);
		}

		$types = array_filter($types, function($item) {
			return !in_array($item, array(
				'baseparams'
			));
		});

		return $types;
	}

	/**
	 * Return all supported types as options for select
	 *
	 * @return array
	 */
	public static function getTypesOptions()
	{
		$types = self::getTypes();
		$options = array();

		foreach ($types as $type)
		{
			$options[] = array('value' => $type, 'text' => JText::_('COM_REDFORM_FIELD_TYPE_' . $type));
		}

		return $options;
	}

	/**
	 * Returns field associated to id
	 *
	 * @param   int  $id  form field id
	 *
	 * @return RdfRfield
	 *
	 * @throws Exception
	 */
	public static function getFormField($id)
	{
		if (empty(static::$fields[$id]))
		{
			$type = static::getType($id);
			$field = static::getFieldType($type);
			$field->setId($id);

			static::$fields[$id] = $field;
		}

		return clone static::$fields[$id];
	}

	/**
	 * Return instance of field type
	 *
	 * @param   string  $type  type
	 *
	 * @return RdfRfield
	 *
	 * @throws Exception
	 */
	public static function getFieldType($type)
	{
		$class = 'RdfRfield' . ucfirst($type);

		if (!class_exists($class, true))
		{
			throw new Exception(JText::sprintf('Field type %s not found', $type));
		}

		$field = new $class;

		return $field;
	}

	/**
	 * Return field type
	 *
	 * @param   int  $formfieldId  form field id
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	protected static function getType($formfieldId)
	{
		$model = new RdfCoreModelFormfield;
		$data = $model->setId($formfieldId)->getItem();

		if (!$data)
		{
			throw new Exception(JText::sprintf('form field %d not found', $formfieldId));
		}

		return $data->fieldtype;
	}
}
