<?php
/**
 * @package    Redform.Site
 *
 * @copyright  Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * redform Component payment Model
 *
 * @package  Redform.Site
 * @since    2.5
 */
class RedFormModelBilling extends RModelAdmin
{
	/**
	 * Cart data from db
	 *
	 * @var object
	 */
	protected $cart;

	/**
	 * contructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 */
	public function __construct($config)
	{
		parent::__construct();

		$this->reference = JFactory::getApplication()->input->get('reference', '');
	}

	/**
	 * Setter
	 *
	 * @param   string  $reference  submit key
	 *
	 * @return object
	 */
	public function setCartReference($reference)
	{
		if (!empty($reference))
		{
			$this->reference = $reference;
		}

		return $this;
	}
}
