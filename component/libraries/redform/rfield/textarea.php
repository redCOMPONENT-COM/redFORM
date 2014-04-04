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
class RedformRfieldTextarea extends RedformRfieldTextfield
{
	protected $type = 'textarea';

	/**
	 * Returns field Input
	 *
	 * @return string
	 */
	public function getInput()
	{
		$properties = $this->getInputProperties();

		return sprintf('<textarea %s>%s</textarea>', $this->propertiesToString($properties), $this->getValue());
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
		$properties['name'] = $this->getFormElementName();
		$properties['id'] = $this->getFormElementId();

		if ($class = trim($this->getParam('class')))
		{
			$properties['class'] = $class;
		}

		$properties['cols'] =  $this->getParam('cols', 25);
		$properties['rows'] =  $this->getParam('rows', 6);

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
}
