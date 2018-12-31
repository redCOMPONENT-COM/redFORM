<?php
/**
 * @package    Redform.plugins
 * @copyright  Copyright (C) 2012 - 2018 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

use Redform\Plugin\AbstractReplaceconditionPlugin;

defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * redFORM Equals Replace condition plugin
 *
 * @package  Redform.plugins
 * @since    __deploy_version__
 */
class PlgRedform_ReplaceconditionEquals extends AbstractReplaceconditionPlugin
{
	/**
	 * @var string
	 */
	protected $name = 'equals';

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
			throw new \InvalidArgumentException('Invalid argument for equals condition');
		}

		$fieldId = substr($condition[1], strlen('field_'));

		$value = $answers->getFieldAnswer($fieldId);

		$isValid = $value == $condition[2];
	}
}
