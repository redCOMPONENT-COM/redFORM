<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Class RdfHelperConditionalrecipients
 *
 * @package     Redform.Libraries
 * @subpackage  Helper
 * @since       2.5
 */
Class RdfHelperConditionalrecipients
{
	/**
	 * return conditional recipients for specified answers
	 *
	 * @param   object  $form     form
	 * @param   object  $answers  answers
	 *
	 * @return array|boolean false if no answer
	 */
	public static function getRecipients($form, $answers)
	{
		if (!$form->cond_recipients)
		{
			return false;
		}

		$recipients = array();
		$conds = explode("\n", $form->cond_recipients);

		foreach ($conds as $c)
		{
			if ($res = static::parseCondition($c, $answers))
			{
				$recipients[] = $res;
			}
		}

		return $recipients;
	}

	/**
	 * returns email if answers match the condition
	 *
	 * @param   string  $conditionline  condition
	 * @param   object  $answers        answers
	 *
	 * @return string|boolean email or false
	 */
	protected static function parseCondition($conditionline, $answers)
	{
		$parts = explode(";", $conditionline);

		if (!count($parts))
		{
			return false;
		}

		// Cleanup
		array_walk($parts, 'trim');

		if (count($parts) < 5)
		{
			// Invalid condition...
			RdfHelperLog::simpleLog('invalid condition formatting' . $conditionline);

			return false;
		}

		// First should be the email address
		if (!JMailHelper::isEmailAddress($parts[0]))
		{
			RdfHelperLog::simpleLog('invalid email in conditional recipient: ' . $parts[0]);

			return false;
		}

		$email = $parts[0];

		// Then the name of the recipient
		if (!$parts[1])
		{
			RdfHelperLog::simpleLog('invalid name in conditional recipient: ' . $parts[0]);

			return false;
		}

		$name = $parts[1];

		// Then, we should get the field
		$field_id = intval($parts[2]);

		$answer = $answers->getFieldAnswer($field_id);

		if ($answer === false)
		{
			RdfHelperLog::simpleLog('invalid field id for conditional recipient: ' . $parts[1]);

			return false;
		}

		$value = $answer['value'];

		$isvalid = false;

		// Then, we should get the function
		switch ($parts[3])
		{
			case 'between':
				if (!isset($parts[5]))
				{
					RdfHelperLog::simpleLog('missing max value in between conditional recipient: ' . $conditionline);
				}

				if (is_numeric($value))
				{
					$value = floatval($value);
					$min = floatval($parts[4]);
					$max = floatval($parts[5]);
					$isvalid = ($value >= $min && $value <= $max ? $email : false);
				}
				else
				{
					$isvalid = strcasecmp($value, $parts[4]) >= 0 && strcasecmp($value, $parts[5]) <= 0;
				}

				break;

			case 'inferior':
				if (is_numeric($value))
				{
					$value = floatval($value);
					$max = floatval($parts[4]);
					$isvalid = ($value <= $max ? $email : false);
				}
				else
				{
					$isvalid = strcasecmp($value, $parts[4]) <= 0;
				}

				break;

			case 'superior':
				if (is_numeric($value))
				{
					$value = floatval($value);
					$min = floatval($parts[4]);
					$isvalid = ($value >= $min ? $email : false);
				}
				else
				{
					$isvalid = strcasecmp($value, $parts[4]) >= 0;
				}

				break;

			case 'equal':
				if (is_numeric($value))
				{
					$value = floatval($value);
					$expected = floatval($parts[4]);
					$isvalid = ($value == $expected ? $email : false);
				}
				else
				{
					$isvalid = strcasecmp($value, $parts[4]) === 0;
				}

				break;

			case 'regex':
				$regex = $parts[4];
				$isvalid = preg_match($regex, $value) ? true : false;

				break;

			default:
				RdfHelperLog::simpleLog('invalid email in conditional recipient: ' . $parts[0]);

				return false;
		}

		if ($isvalid)
		{
			return array($email, $name);
		}
	}
}
