<?php
/**
 * @package    Redform.Library
 *
 * @copyright  Copyright (C) 2009 - 2018 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

namespace Redform\Plugin;

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

/**
 * Tag container
 *
 * @package  Redform.Library
 * @since    __deploy_version__
 */
abstract class AbstractReplaceconditionPlugin extends \JPlugin
{
	protected $autoloadLanguage = true;

	protected $name;

	/**
	 * Add supported condition(s)
	 *
	 * @param   array  $conditions  conditions
	 *
	 * @return void
	 */
	public function onRedformGetConditions(&$conditions)
	{
		$conditions[] = [
			'name'        => $this->name,
			'usage'       => Text::_('PLG_REDFORM_REPLACECONDITION_' . $this->name . '_USAGE'),
			'description' => Text::_('PLG_REDFORM_REPLACECONDITION_' . $this->name . '_DESCRIPTION'),
		];
	}

	/**
	 * Process condition
	 *
	 * @param   array        $condition  condition parts
	 * @param   \RdfAnswers  $answers    answers from submission
	 * @param   boolean      $isValid    return true if valid, else false
	 *
	 * @return void
	 */
	abstract public function onRedformProcessReplaceCondition($condition, \RdfAnswers $answers, &$isValid);
}
