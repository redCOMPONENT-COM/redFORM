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
	 * Field options
	 *
	 * @var array
	 */
	protected $options;

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
	protected $formIndex = 1;

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
	 * Should the label be shown
	 *
	 * @var bool
	 */
	protected $showLabel = true;

	/**
	 * does the field have options (select, radio, etc...)
	 *
	 * @var bool
	 */
	protected $hasOptions = false;

	/**
	 * Magic method
	 *
	 * @param   string  $name  property name
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'id':
				return $this->getId();

			case 'fieldtype':
				return $this->type;

			case 'default':
				return $this->getParam('default');

			case 'value':
				return $this->getValue();

			case 'published':
				return $this->load()->published;

			case 'tooltip':
				return $this->load()->tooltip;

			case 'hasOptions':
				return $this->hasOptions;

			case 'name':
			case 'field':
				return $this->load()->field;
		}

		$trace = debug_backtrace();
		throw new Exception(
			'Undefined property via __get(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			500);
		return null;
	}

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
	abstract public function getInput();

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
	 * Returns field value ready to be saved in database
	 *
	 * @return string
	 */
	public function getDatabaseValue()
	{
		if (is_array($this->value))
		{
			return implode('~~~', $this->value);
		}
		else
		{
			return $this->value;
		}
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
	 * Set field value from post data
	 *
	 * @param   string  $value  value
	 *
	 * @return string new value
	 */
	public function setValueFromPost($value)
	{
		$this->value = $value;

		return $this->value;
	}

	/**
	 * As redform supports multiple form submission at same time, we need to add a suffix to fields to mark which
	 * instance they belong to
	 *
	 * @param   int  $index  form index
	 *
	 * @return void
	 */
	public function setFormIndex($index)
	{
		$this->formIndex = (int) $index;
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
	 * Show the label ?
	 *
	 * @return bool
	 */
	public function displayLabel()
	{
		return $this->showLabel;
	}

	/**
	 * Return price, possibly depending on current field value
	 *
	 * @return float
	 */
	public function getPrice()
	{
		return 0;
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

		if ($this->formIndex)
		{
			$name .= '.' . $this->formIndex;
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
	public function getParam($name, $default = '')
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

			$query->select('f.id, f.field, f.validate, f.tooltip, f.redmember_field, f.fieldtype, f.params, f.readonly');
			$query->select('f.form_id, f.default, f.published');
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

	/**
	 * Return field options (for select, radio, etc...)
	 *
	 * @return mixed
	 */
	protected function getOptions()
	{
		if (!$this->options)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('id, value, label, field_id, price');
			$query->from('#__rwf_values');
			$query->where('published = 1');
			$query->where('field_id = ' . $this->id);
			$query->order('ordering');

			$db->setQuery($query);
			$this->options = $db->loadObjectList();
		}

		return $this->options;
	}

	/**
	 * Return element properties string
	 *
	 * @param   array  $properties  array of property => value
	 *
	 * @return string
	 */
	protected function propertiesToString($properties)
	{
		$strings = array_map(array($this, 'mapProperties'), array_keys($properties), $properties);

		return implode(' ', $strings);
	}

	/**
	 * Call back function
	 *
	 * @param   string  $property  the property
	 * @param   string  $value     the value
	 *
	 * @return string
	 */
	protected function mapProperties($property, $value)
	{
		return $property . '="' . $value . '"';
	}
}
