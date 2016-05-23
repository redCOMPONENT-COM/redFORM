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
				$sku[] = $option->sku ?: parent::getSku() . '_' . $option->id;
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
	 * Returns field Input
	 *
	 * @return string
	 */
	public function getInput()
	{
		$this->getOptions();

		$element = RdfLayoutHelper::render(
			'rform.rfield.checkbox',
			$this,
			'',
			array('component' => 'com_redform')
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

		if (!empty($option->price))
		{
			$properties['price'] = $option->price;

			if (is_numeric($this->getParam('vat')))
			{
				$properties['vat'] = $this->getParam('vat');
			}
		}

		if ($this->load()->readonly && !$app->isAdmin())
		{
			$properties['readonly'] = 'readonly';
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

		$postName = 'field' . $this->load()->id . '_' . (int) $signup;

		$this->value = $input->get($postName, '', 'array');

		return $this->value;
	}
}
