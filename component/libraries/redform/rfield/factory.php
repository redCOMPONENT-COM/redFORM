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
abstract class RDFRfieldFactory extends JObject
{
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
	 * @param   int  $id  field id
	 *
	 * @return RedformRfield
	 *
	 * @throws Exception
	 */
	public static function getField($id)
	{
		static $fields = array();

		if (!isset($fields[$id]))
		{
			$type = static::getType($id);
			$field = static::getFieldType($type);
			$field->setId($id);

			$fields[$id] = $field;
		}

		return $fields[$id];
	}

	/**
	 * Return instance of field type
	 *
	 * @param   string  $type  type
	 *
	 * @return RedformRfield
	 *
	 * @throws Exception
	 */
	public static function getFieldType($type)
	{
		$class = 'RDFRfield' . ucfirst($type);

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
	 * @param   int  $fieldId  field id
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	protected static function getType($fieldId)
	{
		$model = new RedformCoreModelField;
		$data = $model->setId($fieldId)->getItem();

		if (!$data)
		{
			throw new Exception(JText::sprintf('field %d not found', $fieldId));
		}

		return $data->fieldtype;
	}
}
