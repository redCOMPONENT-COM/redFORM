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
class RdfRfieldCheckbox extends RdfRfield
{
	protected $type = 'checkbox';

	protected $hasOptions = true;

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
		if ($value && !is_array($value))
		{
			$value = array($value);
		}

		return parent::setValue($value, $lookup);
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
		$this->value = explode('~~~', $value);

		return $this->value;
	}

	/**
	 * Return price, possibly depending on current field value
	 *
	 * @return float
	 */
	public function getPrice()
	{
		$price = 0;

		if (!$this->value)
		{
			return $price;
		}

		foreach ($this->getOptions() as $option)
		{
			if (in_array($option->value, $this->value))
			{
				$price += $option->price;
			}
		}

		return $price;
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
			$this->value = explode(',', $this->user->get($this->load()->redmember_field));
		}
		elseif ($this->load()->default)
		{
			$values = explode("\n", $this->load()->default);
			$this->value = array_map('trim', $values);
		}

		return $this->value;
	}

	public function getInput()
	{
		$this->getOptions();

		$element = RLayoutHelper::render(
			'rform.rfield.checkbox',
			$this,
			'',
			array('client' => 0, 'component' => 'com_redform')
		);

		return $element;
	}

	/**
	 * Return input properties array
	 *
	 * @param   object  $option  the option
	 *
	 * @return array
	 */
	public function getOptionsProperties($option)
	{
		$app = JFactory::getApplication();

		$properties = array();
		$properties['type'] = 'checkbox';
		$properties['name'] = $this->getFormElementName();
		$properties['class'] = trim($this->getParam('class'));
		$properties['value'] = $option->value;

		if ($option->price)
		{
			$properties['price'] = $option->price;
		}

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

		$value = $this->getValue();

		if ($value && in_array($option->value, $value))
		{
			$properties['checked'] = 'checked';
		}

		return $properties;
	}

	/**
	 * Get postfixed field name for form
	 *
	 * @return string
	 */
	public function getFormElementName()
	{
		$name = parent::getFormElementName() . '[]';

		return $name;
	}
}
