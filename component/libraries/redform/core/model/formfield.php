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
 * Class RdfCoreModelField
 *
 * @package     Redform.Libraries
 * @subpackage  Core.Model
 * @since       2.5
 */
class RdfCoreModelFormfield extends JModelItem
{
	protected $id = 0;

	/**
	 * Set form field id
	 *
	 * @param   int  $id  form field id
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

		$query->select('f.*, ff.form_id, ff.published, ff.unique, ff.validate, ff.readonly');
		$query->select('CASE WHEN (CHAR_LENGTH(f.field_header) > 0) THEN f.field_header ELSE f.field END AS field_header');
		$query->from('#__rwf_fields AS f');
		$query->join('INNER', '#__rwf_form_field AS ff ON ff.field_id = f.id');
		$query->where('ff.id = ' . $this->id);

		$db->setQuery($query);
		$res = $db->loadObject();

		return $res;
	}
}
