<?php
/**
 * @package    Redform.plugins
 * @copyright  Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

use Redform\Plugin\AbstractReplaceconditionPlugin;

defined('_JEXEC') or die;

/**
 * redFORM Selected Replace condition plugin
 *
 * @package  Redform.plugins
 * @since    3.3.26
 */
class PlgRedform_ReplaceconditionSelected extends AbstractReplaceconditionPlugin
{
	/**
	 * @var string
	 */
	protected $name = 'selected';

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

		if (!strstr($condition[1], 'field_'))
		{
			throw new \InvalidArgumentException('Invalid argument for selected condition');
		}

		$fieldId = substr($condition[1], strlen('field_'));

		$value = $answers->getFieldAnswer($fieldId);

		if (!is_array($value))
		{
			return false;
		}

		$isValid = in_array($condition[2], $value);
	}
}
