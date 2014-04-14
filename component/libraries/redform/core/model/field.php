<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Core.Model
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');

/**
 * Class RedformCoreModelField
 *
 * @package     Redform.Libraries
 * @subpackage  Core.Model
 * @since       2.5
 */
class RedformCoreModelField extends JModelItem
{
	protected $id = 0;

	/**
	 * Set field id
	 *
	 * @param   int  $id  field id
	 *
	 * @return $this
	 */
	public function setId($id)
	{
		$this->id = (int) $id;

		return $this;
	}

	/**
	 * Return field object from table
	 *
	 * @return mixed
	 */
	public function getItem()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('f.*');
		$query->select('CASE WHEN (CHAR_LENGTH(f.field_header) > 0) THEN f.field_header ELSE f.field END AS field_header');
		$query->from('#__rwf_fields AS f');
		$query->where('f.id = ' . $this->id);

		$db->setQuery($query);
		$res = $db->loadObject();

		return $res;
	}
}
