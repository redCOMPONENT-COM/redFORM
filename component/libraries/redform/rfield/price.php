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
class RdfRfieldPrice extends RdfRfield
{
	protected $type = 'price';

	protected $hasOptions = true;

	/**
	 * Return price, possibly depending on current field value
	 *
	 * @return float
	 */
	public function getPrice()
	{
		$options = $this->getOptions();

		if (count($options))
		{
			$price = reset($options)->value;
		}
		else
		{
			$price = $this->getValue();
		}

		return $price;
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

		$properties['class'] = 'rfprice';

		if (trim($this->getParam('class')))
		{
			$properties['class'] .= ' ' . trim($this->getParam('class'));
		}

		$properties['value'] = $this->getValue();

		$properties['size'] = $this->getParam('size', 25);
		$properties['maxlength'] = $this->getParam('maxlength', 250);

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
	 * Get form currency
	 *
	 * @return mixed
	 */
	public function getCurrency()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('currency');
		$query->from('#__rwf_forms');
		$query->where('id = ' . $db->quote($this->load()->form_id));

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res;
	}
}
