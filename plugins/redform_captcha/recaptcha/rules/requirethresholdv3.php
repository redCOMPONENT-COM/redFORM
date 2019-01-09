<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Rules
 *
 * @copyright   Copyright (C) 2012 - 2018 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Form\Form;
/**
 * Conditional required rule.
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Rules
 * @since       1.0
 */
class JFormRuleRequirethresholdv3 extends RFormRule
{
	/**
	 * Method to test if two values are not equal. To use this rule, the form
	 * XML needs a validate attribute of equals and a field attribute
	 * that is equal to the field to test against.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 * @param   Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   Form              $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @throws  InvalidArgumentException
	 * @throws  UnexpectedValueException
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		if ($input->get('params')->version == 3 && !$input->get('params')->min_accepted_score_v3)
		{
			return false;
		}
	}
}
