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
class RdfRfieldWysiwyg extends RdfRfieldTextfield
{
	protected $type = 'wysiwyg';

	/**
	 * Get and set the value from post data, using appropriate filtering
	 *
	 * @param   int  $signup  form instance number for the field
	 *
	 * @return mixed
	 */
	public function getValueFromPost($signup)
	{
		$postName = 'field' . $this->load()->id . '_' . (int) $signup;

		$text = isset($_REQUEST[$postName]) ? $_REQUEST[$postName] : '';

		if ($this->getParam('filtering', 'system') == 'raw')
		{
			$this->value = $text;
		}
		else
		{
			$this->value = JComponentHelper::filterText($text);
		}

		return $this->value;
	}
}
