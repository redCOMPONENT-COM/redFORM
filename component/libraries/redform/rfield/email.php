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
class RdfRfieldEmail extends RdfRfieldTextfield
{
	protected $type = 'email';

	/**
	 * Selected newsletters from post
	 * @var array
	 */
	protected $selectedNewsletters = null;

	/**
	 * Set field value from post data
	 *
	 * @param   string  $value  value
	 *
	 * @return string new value
	 *
	 * @throws LogicException
	 */
	public function setValueFromPost($value)
	{
		if (!is_array($value) || !isset($value['email']))
		{
			throw new LogicException('wrong input for email field set value ');
		}

		$this->value = $value['email'];

		if (isset($value['newsletter']))
		{
			$this->selectedNewsletters = $value['newsletter'];
		}

		return $this->value;
	}

	/**
	 * Return selected newsletters
	 *
	 * @return array
	 */
	public function getSelectedNewsletters()
	{
		return $this->selectedNewsletters ? $this->selectedNewsletters : array();
	}

	/**
	 * Returns field Input
	 *
	 * @return string
	 */
	public function getInput()
	{
		$element = RLayoutHelper::render(
			'rform.rfield.email',
			$this,
			'',
			array('client' => 0, 'component' => 'com_redform')
		);

		return $element;
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

		$name .= '[email]';

		return $name;
	}

	/**
	 * Get postfixed field name for form
	 *
	 * @return string
	 */
	public function getFormListElementName()
	{
		$name = 'field' . $this->id;

		if ($this->formIndex)
		{
			$name .= '.' . $this->formIndex;
		}

		$name .= '[newsletter][]';

		return $name;
	}

	/**
	 * Return input properties array
	 *
	 * @return array
	 */
	public function getInputProperties()
	{
		$properties = parent::getInputProperties();

		if (isset($properties['class']) && $properties['class'])
		{
			$properties['class'] .= ' validate-email';
		}
		else
		{
			$properties['class'] = 'validate-email';
		}

		return $properties;
	}

	/**
	 * Check that data is valid
	 *
	 * @return bool
	 */
	public function validate()
	{
		if (!parent::validate())
		{
			return false;
		}

		if ($this->value && !JMailHelper::isEmailAddress($this->value))
		{
			$this->setError(JText::_('COM_REDFORM_INVALID_EMAIL_FORMAT'));

			return false;
		}

		return true;
	}

	/**
	 * Try to get a default value from integrations
	 *
	 * @return void
	 */
	protected function lookupDefaultValue()
	{
		if ($this->formIndex == 1 && $this->user && $this->user->email)
		{
			$this->value = $this->user->email;
		}
		else
		{
			$this->value = parent::lookupDefaultValue();
		}

		return $this->value;
	}
}
