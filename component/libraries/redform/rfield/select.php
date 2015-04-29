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
class RdfRfieldSelect extends RdfRfield
{
	protected $type = 'select';

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
	 * Get and set the value from post data, using appropriate filtering
	 *
	 * @param   int  $signup  form instance number for the field
	 *
	 * @return mixed
	 */
	public function getValueFromPost($signup)
	{
		$value = parent::getValueFromPost($signup);

		if ($value && !is_array($value))
		{
			$this->value = array($value);
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
	 * SKU associated to price
	 *
	 * @return string
	 */
	public function getSku()
	{
		$sku = array();

		if (!$this->value)
		{
			return '';
		}

		foreach ($this->getOptions() as $option)
		{
			if (in_array($option->value, $this->value))
			{
				$sku[] = $option->sku;
			}
		}

		return implode('-', $sku);
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
	 * @return array
	 */
	public function getSelectProperties()
	{
		$app = JFactory::getApplication();

		$properties = array();
		$properties['name'] = $this->getFormElementName();
		$properties['class'] = trim($this->getParam('class'));
		$properties['size'] = $this->getParam('size', 5);

		if ($this->getParam('multiple'))
		{
			$properties['multiple'] = 'multiple';
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

		return $properties;
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

			if (is_numeric($this->getParam('vat')))
			{
				$properties['vat'] = $this->getParam('vat');
			}
		}

		$value = $this->getValue();

		if ($value && in_array($option->value, $value))
		{
			$properties['selected'] = 'selected';
		}

		return $properties;
	}
}
