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
class RedformRfieldWysiwyg extends RedformRfieldTextfield
{
	protected $type = 'wysiwyg';

	/**
	 * Returns field Input
	 *
	 * @return string
	 */
	public function getInput()
	{
		$editor = JFactory::getEditor();
		$element = $editor->display($this->getFormElementName(), $this->getValue(), '100%;', '200', '75', '20', false);

		return $element;
	}
}
