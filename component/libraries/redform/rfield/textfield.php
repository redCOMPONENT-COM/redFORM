<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Rfield
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

/**
 * redFORM field
 *
 * @package     Redform.Libraries
 * @subpackage  Rfield
 * @since       2.5
 */
class RdfRfieldTextfield extends RdfRfield
{
	protected $type = 'textfield';

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

		if ($class = trim($this->getParam('class')))
		{
			$properties['class'] = $class;
		}

		$properties['value'] = $this->getValue() ? $this->getValue() : $this->default;

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

		if ($regex = $this->getParam('regexformat'))
		{
			$properties['regex'] = addslashes($regex);
			$properties['regex_info'] = addslashes(JText::_($this->getParam('regexformat_desc')));

			if (isset($properties['class']))
			{
				$properties['class'] .= ' validate-custom' . $this->id;
			}
			else
			{
				$properties['class'] = ' validate-custom' . $this->id;
			}
		}

		return $properties;
	}

	/**
	 * Returns field Input
	 *
	 * @return string
	 */
	public function getInput()
	{
		if ($regex = $this->getParam('regexformat'))
		{
			$script = <<<JS
	(function($){
		$(function() {
			document.redformvalidator.setHandler('custom{$this->id}', function (value, element) {
				if ($(element).attr('regex')) {
					var regexP = $(element).attr('regex');
					var result = value.match(regexP) ? true : false;

					document.redformvalidator.setElementError(element.get(0), result ? '' : $(element).attr('regex_info'));

					return result;
				}

				return true;
			});
		});
	})(jQuery);
JS;
			JFactory::getDocument()->addScriptDeclaration($script);
		}

		return parent::getInput();
	}

	/**
	 * Check that data is valid
	 *
	 * @param   RdfRfield[]  $fields  all fields with value
	 *
	 * @return boolean
	 */
	public function validate($fields)
	{
		if (!parent::validate($fields))
		{
			return false;
		}

		$regex = $this->getParam('regexformat');
		$value = $this->getValue();

		if ($value && $regex && !preg_match("/" . $regex . "/", $value))
		{
			$this->setError($this->name . ': ' . $this->getParam('regexformat_desc'));

			return false;
		}

		$limit = (int) $this->getParam('maxlength');

		if ($limit && strlen($value) > $limit)
		{
			$this->setError(
				Text::sprintf(
					'LIB_REDFORM_VALIDATE_FIELD_ERROR_TOO_LONG',
					$this->name,
					$limit
				)
			);

			return false;
		}

		return true;
	}
}
