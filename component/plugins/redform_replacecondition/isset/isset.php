<?php
/**
 * @package    Redform.plugins
 * @copyright  Copyright (C) 2012 - 2018 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

use Redform\Plugin\AbstractReplaceconditionPlugin;

defined('_JEXEC') or die;

/**
 * redFORM Isset Replace condition plugin
 *
 * @package  Redform.plugins
 * @since    __deploy_version__
 */
class PlgRedform_ReplaceconditionIsset extends AbstractReplaceconditionPlugin
{
	/**
	 * @var string
	 */
	protected $name = 'isset';

	/**
	 * Process condition
	 *
	 * @param   array        $condition  condition parts
	 * @param   \RdfAnswers  $answers    answers from submission
	 * @param   boolean      $isValid    return true if valid, else false
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	public function onRedformProcessReplaceCondition($condition, \RdfAnswers $answers, &$isValid)
	{
		if ($condition[0] !== $this->name)
		{
			return;
		}

		$isValid = false;

		if (!strstr($condition[1], 'field_'))
		{
			throw new \InvalidArgumentException('Invalid argument for isset condition');
		}

		$fieldId = substr($condition[1], strlen('field_'));

		foreach ($answers->getFields() as $field)
		{
			if ($field->fieldId == $fieldId)
			{
				$isValid = true;

				break;
			}
		}

		return;
	}
}
