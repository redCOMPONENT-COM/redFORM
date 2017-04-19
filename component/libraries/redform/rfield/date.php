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
class RdfRfieldDate extends RdfRfield
{
	protected $type = 'date';

	/**
	 * Returns field Input
	 *
	 * @return string
	 */
	public function getInput()
	{
		$element = RdfLayoutHelper::render(
			'rform.rfield.date',
			$this,
			'',
			array('component' => 'com_redform')
		);

		return $element;
	}

	/**
	 * Try to get a default value from integrations
	 *
	 * @return void
	 */
	public function lookupDefaultValue()
	{
		$format = $this->getParam('dateformat', '%Y-%m-%d');

		$default = $this->getLookupDefaultValueIntegration();

		if (!is_null($default))
		{
			$this->value = $default;
		}
		elseif ($this->load()->redmember_field)
		{
			$this->value = strftime($format, $this->user->get($this->load()->redmember_field));
		}
		elseif ($this->load()->default && strtotime($this->load()->default))
		{
			$this->value = strftime($format, strtotime($this->load()->default));
		}

		if ($this->value && !strtotime($this->value))
		{
			// Invalid
			$val = null;
		}

		return $this->value;
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
		$properties['name'] = $this->getFormElementName();
		$properties['id'] = $this->getFormElementId();

		$class = array();

		if (trim($this->getParam('class')))
		{
			$class = array_merge($class, explode(" ", trim($this->getParam('class'))));
		}

		$properties['value'] = $this->getValue();

		$properties['dateformat'] = $this->getParam('dateformat', '%Y-%m-%d');

		if ($this->load()->readonly && !$app->isAdmin())
		{
			$properties['readonly'] = 'readonly';
		}

		if ($this->load()->validate)
		{
			$class[] = 'required';
		}

		if ($this->getParam('future'))
		{
			$class[] = 'validate-futuredate';
		}

		if ($placeholder = $this->getParam('placeholder'))
		{
			$properties['placeholder'] = addslashes($placeholder);
		}

		$properties['class'] = implode(" ", $class);

		return $properties;
	}
}
