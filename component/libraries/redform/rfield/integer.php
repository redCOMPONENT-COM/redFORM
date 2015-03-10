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
class RdfRfieldInteger extends RdfRfieldSelect
{
	protected $type = 'integer';

	protected $hasOptions = false;

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
	public function setValueFromPost($value)
	{
		if ($value && !is_array($value))
		{
			$value = array($value);
		}

		return parent::setValueFromPost($value);
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
	public function lookupDefaultValue()
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

	/**
	 * Get postfixed field name for form
	 *
	 * @return string
	 */
	public function getFormElementName()
	{
		$name = parent::getFormElementName();

		if ($this->getParam('multiple'))
		{
			$name .= '[]';
		}

		return $name;
	}

	/**
	 * Return input properties array
	 *
	 * @param   object  $option  the option
	 *
	 * @return array
	 */
	public function getOptionProperties($option)
	{
		$properties = array();
		$properties['value'] = $option->value;

		if ($option->price)
		{
			$properties['price'] = $option->price;
		}

		$value = $this->getValue();

		if ($value && in_array($option->value, $value))
		{
			$properties['selected'] = 'selected';
		}

		return $properties;
	}

	/**
	 * Return field options (for select, radio, etc...)
	 *
	 * @return mixed
	 */
	public function getOptions()
	{
		if (!$this->options)
		{
			$min = (int) $this->getParam('minvalue');
			$max = (int) $this->getParam('maxvalue');
			$step = (int) $this->getParam('step', 1) > 0 ? (int) $this->getParam('step', 1) : 1;
			$price = floatval($this->getParam('baseprice'));

			$options = array();
			$current = $min;

			while ($current <= $max)
			{
				$obj = new stdClass;
				$obj->label = $current;
				$obj->value = $current;
				$obj->price = $current * $price;
				$options[] = $obj;

				$current += $step;
			}

			$this->options = $options;
		}

		return $this->options;
	}
}
