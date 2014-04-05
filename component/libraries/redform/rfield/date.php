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
class RedformRfieldDate extends RedformRfield
{
	protected $type = 'date';

	/**
	 * Returns field Input
	 *
	 * @return string
	 */
	public function getInput()
	{
		$properties = $this->getInputProperties();

		if (isset($properties['readonly']))
		{
			return $this->getReadonlyField($properties);
		}
		else
		{
			return $this->getDateField($properties);
		}
	}

	protected function getReadonlyField($properties)
	{
		$properties['type'] = 'hidden';

		return sprintf('<input %s/>%s', $this->propertiesToString($properties), $this->getValue());
	}

	protected function getDateField($properties)
	{
		$attribs = array();

		if (isset($properties['class']))
		{
			$attribs['class'] = $properties['class'];
		}

		$element = JHTML::_('calendar', $this->getValue(), $properties['name'], $properties['id'],
			$this->getParam('dateformat', '%Y-%m-%d'),
			$attribs
		);

		return $element;
	}

	/**
	 * Try to get a default value from integrations
	 *
	 * @return void
	 */
	protected function lookupDefaultValue()
	{
		$format = $this->getParam('dateformat', '%Y-%m-%d');

		if ($this->load()->redmember_field)
		{
			$this->value = strftime($format, $this->user->get($this->load()->redmember_field));
		}
		elseif ($this->load()->default && strtotime($this->load()->default))
		{
			$this->value = strftime($format, $this->load()->default);
		}

		if ($this->value && !strtotime($this->value))
		{
			// invalid
			$val = null;
		}

		return $this->value;
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

		if ($class = trim($this->getParam('class')))
		{
			$properties['class'] = $class;
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
			if (isset($properties['class']))
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
}
