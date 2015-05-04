<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Class RdfCorePaymentCart
 *
 * @package     Redform.Libraries
 * @subpackage  Helper
 * @since       2.5
 */
class RdfCorePaymentCart
{
	protected $data;

	/**
	 * Load cart by id
	 *
	 * @param   int  $id  cart id
	 *
	 * @return $this
	 */
	public function loadById($id)
	{
		$table = RTable::getAdminInstance('Cart', array(), 'com_redform');
		$table->load($id);
		$this->data = $table;

		return $this;
	}

	/**
	 * Load cart by reference
	 *
	 * @param   string  $reference  reference
	 *
	 * @return $this
	 */
	public function loadByReference($reference)
	{
		$table = RTable::getAdminInstance('Cart', array(), 'com_redform');
		$table->load(array('reference' => $reference));
		$this->data = $table;

		return $this;
	}

	/**
	 * Getter
	 *
	 * @param   string  $name  property
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 */
	public function __get($name)
	{
		if (isset($this->data->{$name}))
		{
			return $this->data->{$name};
		}

		throw new Exception('Property not found or not accessible: ' . $name);
	}
}
