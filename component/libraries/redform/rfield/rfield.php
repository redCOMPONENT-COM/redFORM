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
abstract class RdfRfield extends JObject
{
	/**
	 * Field type name
	 * @var string
	 */
	protected $type;

	/**
	 * Form field id
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

			case 'fieldId':
				return $this->load()->field_id;

			case 'fieldtype':
				return $this->type;

			case 'value':
				return $this->getValue();

			case 'published':
				return $this->load()->published;

			case 'tooltip':
				return $this->load()->tooltip;

			case 'hasOptions':
				return $this->hasOptions;

			case 'options':
				return $this->getOptions();

			case 'name':
			case 'field':
				return $this->load()->field;

			case 'redmember_field':
				return $this->load()->redmember_field;

			case 'required':
			case 'validate':
				return $this->load()->validate;

			default:
				$data = $this->load();

				if (isset($data->{$name}))
				{
					return $data->{$name};
				}
		}

		$trace = debug_backtrace();
		throw new Exception(
			'Undefined property via __get(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			500
		);

		return null;
	}

	/**
	 * Get field xml for configuration
	 *
	 * @return string
	 */
	public function getXmlPath()
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

		$label = RdfHelperLayout::render(
			'rform.rfield.label',
			$this,
			'',
			array('client' => 0, 'component' => 'com_redform')
		);

		return $label;
	}

	/**
	 * Returns field Input
	 *
	 * @return string
	 */
	public function getInput()
	{
		$element = RdfHelperLayout::render(
			'rform.rfield.' . $this->type,
			$this,
			'',
			array('client' => 0, 'component' => 'com_redform')
		);

		return $element;
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
	 * Returns field value ready to be printed.
	 * Array values will be separated with ~~~
	 *
	 * @return string
	 */
	public function getValueAsString()
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
	 * Set field value from post data
	 *
	 * @param   string  $value  value
	 *
	 * @return string new value
	 */
	public function setValueFromDatabase($value)
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
	 * Return input properties array
	 *
	 * @return array
	 */
	public function getInputProperties()
	{
		$app = JFactory::getApplication();

		$properties = array();
		$properties['type'] = 'text';
		$properties['name'] = $this->getFormElementName();
		$properties['id'] = $this->getFormElementId();

		if ($class = trim($this->getParam('class')))
		{
			$properties['class'] = $class;
		}

		return $properties;
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
	public function getFormElementName()
	{
		$name = 'field' . $this->id;

		if ($this->formIndex)
		{
			$name .= '_' . $this->formIndex;
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
	 * Force field data rather than pulling from db
	 *
	 * @param   mixed  $data  data as array or object
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function setData($data)
	{
		if (is_object($data))
		{
			$this->data = $data;
		}
		elseif (is_array($data))
		{
			$this->data = (object) $data;
		}
	}

	/**
	 * Check that data is valid
	 *
	 * @return bool
	 */
	public function validate()
	{
		$data = $this->load();

		if ($data->validate && !$this->getValue())
		{
			$this->setError(JText::sprintf('COM_REDFORM_FIELD_S_IS_REQUIRED', $data->name));

			return false;
		}

		return true;
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

			$query->select('f.field, f.tooltip, f.redmember_field, f.fieldtype, f.params, f.default');
			$query->select('ff.id, ff.field_id, ff.validate, ff.readonly');
			$query->select('ff.form_id, ff.published');
			$query->select('CASE WHEN (CHAR_LENGTH(f.field_header) > 0) THEN f.field_header ELSE f.field END AS field_header');
			$query->from('#__rwf_form_field AS ff');
			$query->join('INNER', '#__rwf_fields AS f ON ff.field_id = f.id');
			$query->where('ff.id = ' . $this->id);
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
			$query->where('field_id = ' . $this->load()->field_id);
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
	public function propertiesToString($properties)
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
