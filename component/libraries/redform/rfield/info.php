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
class RdfRfieldInfo extends RdfRfield
{
	protected $type = 'info';

	/**
	 * Should the label be shown
	 *
	 * @var bool
	 */
	protected $showLabel = false;

	/**
	 * Returns field Input
	 *
	 * @return string
	 */
	public function getInput()
	{
		$class = array('infofield');

		if ($this->getParam('class'))
		{
			$class[] = $this->getParam('class');
		}

		$text = sprintf('<div class="%s">%s</div>', implode('', $class), $this->load()->default);

		return $text;
	}
}
