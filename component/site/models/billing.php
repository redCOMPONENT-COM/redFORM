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
class RedformModelBilling extends RModelAdmin
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

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   11.1
	 */
	public function getItem($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();

		if ($pk > 0)
		{
			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());
				return false;
			}
		}
		else
		{
			JPluginHelper::importPlugin('redform');
			$dispatcher = JDispatcher::getInstance();

			$user = JFactory::getUser();
			$prefilled = false;
			$dispatcher->trigger('onRedformPrefillBilling', array($this->reference, &$table, &$prefilled));

			if (!$prefilled && $user->id)
			{
				$table->fullname = $user->name;
				$table->email = $user->email;
			}
		}

		// Convert to the JObject before adding other data.
		$properties = $table->getProperties(1);
		$item = JArrayHelper::toObject($properties, 'JObject');

		if (property_exists($item, 'params'))
		{
			$registry = new JRegistry;
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}

		return $item;
	}
}
