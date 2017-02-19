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
class RdfRfieldDatetimepicker extends RdfRfield
{
	protected $type = 'datetimepicker';

	/**
	 * Returns field Input
	 *
	 * @return string
	 */
	public function getInput()
	{
		$app = JFactory::getApplication();

		$class = array();

		if (trim($this->getParam('class')))
		{
			$class = array_merge($class, explode(" ", trim($this->getParam('class'))));
		}

		$picker = $this->getParam('picker');
		$dateformat = (string) $this->getParam('dateformat', 'yy-mm-dd');
		$timeformat = (string) $this->getParam('timeformat', 'HH:mm:ss');
		$altDateformat = (string) $this->getParam('altDateformat', $dateformat);
		$altTimeformat = (string) $this->getParam('altTimeformat', $timeformat);
		$showSecond = (boolean) $this->getParam('showSecond', false);
		$minDate = (string) $this->getParam('minDate');
		$maxDate = (string) $this->getParam('maxDate');

		$readonly = ($this->load()->readonly && !$app->isAdmin());

		switch ($picker)
		{
			case 'date':
				$layout = 'rform.rfield.datetimepicker.datepicker';
				break;

			case 'time':
				$layout = 'rform.rfield.datetimepicker.timepicker';
				break;

			case 'datetime':
			default:
				$layout = 'rform.rfield.datetimepicker.datetimepicker';
				break;
		}

		return RdfLayoutHelper::render(
			$layout,
			array(
				'field'         => $this,
				'class'         => implode(" ", $class),
				'id'            => $this->getFormElementId(),
				'required'      => $this->load()->validate,
				'name'          => $this->getFormElementName(),
				'dateformat'    => $dateformat,
				'timeformat'    => $timeformat,
				'altDateformat' => $altDateformat,
				'altTimeformat' => $altTimeformat,
				'showSecond'    => $showSecond,
				'readonly'      => $readonly,
				'minDate'       => $minDate,
				'maxDate'       => $maxDate,
				'value'         => $this->getValue()
			),
			'',
			array('component' => 'com_redform')
		);
	}

	/**
	 * Try to get a default value from integrations
	 *
	 * @return void
	 */
	public function lookupDefaultValue()
	{
		$format = $this->getParam('dateformat', 'yy-mm-dd');

		if ($this->load()->redmember_field)
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
}
