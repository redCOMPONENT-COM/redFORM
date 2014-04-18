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
class RdfRfieldFullname extends RdfRfieldTextfield
{
	protected $type = 'fullname';

	/**
	 * Try to get a default value from integrations
	 *
	 * @return void
	 */
	protected function lookupDefaultValue()
	{
		if ($this->formIndex == 1 && $this->user && $this->user->name)
		{
			$this->value = $this->user->name;
		}
		else
		{
			$this->value = parent::lookupDefaultValue();
		}

		return $this->value;
	}
}
