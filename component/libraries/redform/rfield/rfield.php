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
 * redFORM field
 *
 * @package     Redform.Libraries
 * @subpackage  Rfield
 * @since       2.5
 */
abstract class RedformRfield extends JObject
{
	/**
	 * Field type name
	 * @var string
	 */
	protected $type;

	/**
	 * Field id
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Field data from
	 * @var null
	 */
	protected $data = null;

	/**
	 * Field value
	 *
	 * @var null
	 */
	protected $value = null;

	/**
	 * Field Parameters
	 * @var JRegistry
	 */
	protected $params;

	/**
	 * As redform supports multiple form submission at same time, we need to add a suffix to fields to mark which
	 * instance they belong to
	 *
	 * @var int
	 */
	protected $formCount = 1;

	/**
	 * User associated to submission, for value lookup
	 *
	 * @var JUser
	 */
	protected $user;

	/**
	 * Is the field hidden
	 *
	 * @var bool
	 */
	protected $hidden = false;

	/**
	 * Get field xml for configuration
	 *
	 * @return string
	 */
	public function getXml()
	{
		return __DIR__ . '/' . $this->type . '.xml';
	}

	/**
	 * Set field id
	 *
	 * @param   int  $id  field id
	 *
	 * @return void
	 */
	public function setId($id)
	{
		$this->id = (int) $id;
	}

	/**
	 * Get field id
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set user
	 *
	 * @param   object  $user  user associated to field
	 *
	 * @return void
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}

	/**
	 * Returns field label
	 *
	 * @return string
	 */
	public function getLabel()
	{
		$data = $this->load();

		return '<label for="' . $this->getFormElementName() . '">' . $data->field . '</label>';
	}

	/**
	 * Returns field Input
	 *
	 * @return string
	 */
	public function getInput()
	{
		return sprintf('<input %s/>', $this->getInputPropertiesString());
	}

	/**
	 * Returns field value
	 *
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Set field value, try to look up if null
	 *
	 * @param   string  $value   value
	 * @param   bool    $lookup  set true to lookup for a default value if value is null
	 *
	 * @return string new value
	 */
	public function setValue($value, $lookup = false)
	{
		$this->value = $value;

		if (is_null($this->value) && $lookup)
		{
			$this->lookupDefaultValue();
		}

		return $this->value;
	}

	/**
	 * As redform supports multiple form submission at same time, we need to add a suffix to fields to mark which
	 * instance they belong to
	 *
	 * @param   int  $count  form count
	 *
	 * @return void
	 */
	public function setFormCount($count)
	{
		$this->formCount = (int) $count;
	}

	/**
	 * Is hidden ?
	 *
	 * @return bool
	 */
	public function isHidden()
	{
		return $this->hidden;
	}

	/**
	 * Is required ?
	 *
	 * @return bool
	 */
	public function isRequired()
	{
		return $this->load()->validate;
	}

	/**
	 * Try to get a default value from integrations
	 *
	 * @return void
	 */
	protected function lookupDefaultValue()
	{
		if ($this->load()->redmember_field)
		{
			$this->value = $this->user->get($this->load()->redmember_field);
		}
		else
		{
			$this->value = $this->load()->default;
		}

		return $this->value;
	}

	/**
	 * Get postfixed field name for form
	 *
	 * @return string
	 */
	protected function getFormElementName()
	{
		$name = 'field' . $this->id;

		if ($this->formCount)
		{
			$name .= '.' . $this->formCount;
		}

		return $name;
	}

	/**
	 * Get postfixed field id for form
	 *
	 * @return string
	 */
	protected function getFormElementId()
	{
		return $this->getFormElementName();
	}

	/**
	 * Get parameter value
	 *
	 * @param   string  $name     parameter name
	 * @param   string  $default  default value
	 *
	 * @return string
	 */
	protected function getParam($name, $default = '')
	{
		return $this->getParameters()->get($name, $default);
	}

	/**
	 * Get field parameters
	 *
	 * @return JRegistry
	 */
	protected function getParameters()
	{
		if (!$this->params)
		{
			$data = $this->load();

			$this->params = new JRegistry;
			$this->params->loadString($data->params);
		}

		return $this->params;
	}

	/**
	 * Get input properties as string
	 *
	 * @return string
	 */
	protected function getInputPropertiesString()
	{
		$res = array();

		foreach ($this->getInputProperties() as $property => $value)
		{
			$res[] = $property . '="' . $value . '"';
		}

		return implode(" ", $res);
	}

	/**
	 * Return input properties array
	 *
	 * @return array
	 */
	protected function getInputProperties()
	{
		$app = JFactory::getApplication();

		$properties = array();
		$properties['type'] = 'text';
		$properties['name'] = $this->getFormElementName();
		$properties['id'] = $this->getFormElementId();
		$properties['class'] = trim($this->getParam('class'));
		$properties['value'] = $this->getValue();

		if ($this->load()->readonly && !$app->isAdmin())
		{
			$properties['readonly'] = 'readonly';
		}

		if ($this->load()->validate)
		{
			if ($properties['class'])
			{
				$properties['class'] .= ' required';
			}
			else
			{
				$properties['class'] = ' required';
			}
		}

		if ($placeholder = $this->getParam('placeholder'))
		{
			$properties['placeholder'] = addslashes($placeholder);
		}

		return $properties;
	}

	/**
	 * Load field data from database
	 *
	 * @return mixed|null
	 *
	 * @throws Exception
	 */
	protected function load()
	{
		if ((!$this->data) && $this->id)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('f.id, f.field, f.validate, f.tooltip, f.redmember_field, f.fieldtype, f.params, f.readonly, f.default, f.published');
			$query->select('CASE WHEN (CHAR_LENGTH(f.field_header) > 0) THEN f.field_header ELSE f.field END AS field_header');
			$query->from('#__rwf_fields AS f');
			$query->where('f.id = ' . $this->id);
			$db->setQuery($query);
			$this->data = $db->loadObject();

			if (!$this->data)
			{
				throw new Exception(JText::sprintf('COM_REDFORM_LIB_REDFORMFIELD_FIELD_NOT_FOUND_S', $this->id));
			}
		}

		return $this->data;
	}
}
