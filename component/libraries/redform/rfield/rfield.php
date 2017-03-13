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
class RdfRfield extends JObject
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
	 * Form field section id
	 * @var int
	 */
	protected $section_id = 0;

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
	 * Form field section id
	 * @var RdfEntityForm
	 */
	protected $form;

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
	 * Price item label
	 *
	 * @var null
	 */
	protected $paymentRequestItemLabel = null;

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

			case 'section_id':
				$data = $this->load();

				if (property_exists($data, $name))
				{
					return $data->{$name};
				}

				return $this->section_id;

			case 'required':
			case 'validate':
				return $this->load()->validate;

			default:
				$data = $this->load();

				if (property_exists($data, $name))
				{
					return $data->{$name};
				}
		}

		$trace = debug_backtrace();
		throw new Exception(
			sprintf(
				"Undefined property via __get(): %s in %s on line %s\nForm field %s. field %s (%s)",
				$name,
				$trace[0]['file'],
				$trace[0]['line'],
				$this->getId(),
				$this->load()->field_id,
				$this->load()->field
			),
			500
		);

		return null;
	}

	/**
	 * Magic function
	 *
	 * @param   string  $name  property to check
	 *
	 * @return bool
	 *
	 * @since 3.3.18
	 */
	public function __isset($name)
	{
		switch ($name)
		{
			case 'id':
			case 'fieldId':
			case 'fieldtype':
			case 'value':
			case 'published':
			case 'tooltip':
			case 'hasOptions':
			case 'options':
			case 'name':
			case 'field':
			case 'redmember_field':
			case 'section_id':
			case 'required':
			case 'validate':
				return true;

			default:
				$data = $this->load();

				if (property_exists($data, $name))
				{
					return true;
				}
		}
	}

	/**
	 * Get field xml for configuration
	 *
	 * @return string
	 */
	public function getXmlPath()
	{
		$currentDir = dirname((new ReflectionClass(static::class))->getFileName());

		return $currentDir . '/' . $this->type . '.xml';
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

		$label = RdfLayoutHelper::render(
			'rform.rfield.label',
			$this,
			'',
			array('component' => 'com_redform')
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
		$element = RdfLayoutHelper::render(
			'rform.rfield.' . $this->type,
			$this,
			'',
			array('component' => 'com_redform')
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
	 * Array values will be separated with separator (default '~~~')
	 *
	 * @param   string  $separator  separator
	 *
	 * @return string
	 */
	public function getValueAsString($separator = '~~~')
	{
		if (is_array($this->value))
		{
			return implode($separator, $this->value);
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
	 * Get and set the value from post data, using appropriate filtering
	 *
	 * @param   int  $signup  form instance number for the field
	 *
	 * @return mixed
	 */
	public function getValueFromPost($signup)
	{
		$input = JFactory::getApplication()->input;
		$this->value = $input->getString($this->getPostName($signup), '');

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
	 * Form entity
	 *
	 * @param   RdfEntityForm  $form  form entity
	 *
	 * @return void
	 */
	public function setForm(RdfEntityForm $form)
	{
		$this->form = $form;
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
	 * Return vat, possibly depending on current field value
	 *
	 * @return float
	 */
	public function getVat()
	{
		$vatRate = (float) $this->getParam('vat');
		$price = $this->getPrice();

		if ($price && is_numeric($vatRate))
		{
			return $price * $vatRate / 100;
		}

		return 0;
	}

	/**
	 * SKU associated to price
	 *
	 * @return string
	 */
	public function getSku()
	{
		return 'FIELD' . $this->id;
	}

	/**
	 * Get customized label for price item
	 *
	 * @return string
	 */
	public function getPaymentRequestItemLabel()
	{
		return $this->paymentRequestItemLabel ?: $this->load()->field;
	}

	/**
	 * Set customized label for price item
	 *
	 * @param   string  $label  the text to use as label
	 *
	 * @return string
	 */
	public function setPaymentRequestItemLabel($label)
	{
		return $this->paymentRequestItemLabel = $label;
	}

	/**
	 * Return input properties array
	 *
	 * @return array
	 */
	public function getInputProperties()
	{
		$properties = array();
		$properties['type'] = 'text';
		$properties['name'] = $this->getFormElementName();
		$properties['id'] = $this->getFormElementId();

		if ($class = trim($this->getParam('class')))
		{
			$properties['class'] = $class;
		}

		if ($showon = $this->getParam('showon'))
		{
			$showon   = explode(':', $showon, 2);
			$properties['class'] .= ' showon_' . implode(' showon_', explode(',', $showon[1]));
			$id = $this->getName($showon[0]);
			$properties['rel'] = ' rel="showon_' . $id . '"';
			$options['showonEnabled'] = true;
		}

		return $properties;
	}

	/**
	 * Try to get a default value from integrations
	 *
	 * @return void
	 */
	public function lookupDefaultValue()
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
			$this->setError(JText::sprintf('COM_REDFORM_FIELD_S_IS_REQUIRED', $this->name));

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
			$query->select('ff.id, ff.field_id, ff.validate, ff.readonly, ff.section_id');
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

			$query->select('id, value, label, field_id, price, sku');
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
	 * Return the 'value' to be displayed to end user,
	 *
	 * @param   string  $glue  glue to be used if the value is an array
	 *
	 * @return string
	 *
	 * @since 3.3.18
	 */
	public function renderValue($glue = ", ")
	{
		return $this->getValueAsString();
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

	/**
	 * Return field form name with signup offset
	 *
	 * @param   int  $signup  signup id
	 *
	 * @return string
	 */
	protected function getPostName($signup)
	{
		return 'field' . $this->load()->id . '_' . (int) $signup;
	}
}
