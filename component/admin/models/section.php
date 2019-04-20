<?php
/**
 * @package     Redform.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Section Model
 *
 * @package     Redform.Backend
 * @subpackage  Models
 * @since       3.3.8
 */
class RedformModelSection extends RModelAdmin
{

	/**
	 * copy section(s)
	 *
	 * @param   array  $field_ids  field ids
	 *
	 * @return boolean true on success
	 */
	public function copy($ids)
	{
		foreach ($ids as $id)
		{
			$row = $this->getTable('Section', 'RedformTable');
			$row->load($id);
			$row->id = null;
			$row->name = Jtext::_('COM_REDFORM_COPY_OF') . ' ' . $row->name;

			// Pre-save checks
			if (!$row->check())
			{
				$this->setError(JText::_('COM_REDFORM_THERE_WAS_A_PROBLEM_CHECKING_THE_FIELD_DATA'), 'error');

				return false;
			}

			// Save the changes
			if (!$row->store())
			{
				$this->setError(JText::_('COM_REDFORM_THERE_WAS_A_PROBLEM_STORING_THE_FIELD_DATA'), 'error');

				return false;
			}
		}

		return true;
	}
}
