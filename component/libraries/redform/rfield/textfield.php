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
class RedformRfieldTextfield extends RedformRfield
{
	protected $type = 'textfield';

	protected function getInputProperties()
	{
		$properties = parent::getInputProperties();

		$properties['size'] =  $this->getParam('size', 25);
		$properties['maxlength'] =  $this->getParam('maxlength', 250);

		return $properties;
	}
}
