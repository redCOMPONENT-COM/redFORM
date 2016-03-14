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
class RdfRfieldRadio extends RdfRfield
{
	protected $type = 'radio';

	protected $hasOptions = true;

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
			if ($option->value == $this->getValue())
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
			if ($option->value == $this->getValue())
			{
				$sku[] = $option->sku ?: parent::getSku() . '_' . $option->id;
			}
		}

		if (empty($sku))
		{
			return parent::getSku();
		}

		return implode('-', $sku);
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
		$app = JFactory::getApplication();

		$properties = array();
		$properties['type'] = 'radio';
		$properties['name'] = $this->getFormElementName();
		$properties['class'] = trim($this->getParam('class'));
		$properties['value'] = $option->value;

		if ($option->price)
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

		if ($value == $option->value)
		{
			$properties['checked'] = 'checked';
		}

		return $properties;
	}
}
