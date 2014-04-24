<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Core.Model
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Class RdfCoreModelSubmission
 *
 * @package     Redform.Libraries
 * @subpackage  Core.Model
 * @since       3.0
 */
class RdfCoreModelSubmission extends RModel
{
	/**
	 * return answers of specified sids
	 *
	 * @param   array  $sids  submission ids
	 *
	 * @return array
	 */
	public function getSidsAnswers($sids)
	{
		$db = JFactory::getDbo();

		if (empty($sids))
		{
			return false;
		}

		if (!is_array($sids))
		{
			if (is_int($sids))
			{
				$ids = $sids;
			}
			else
			{
				JErrorRaiseWarning(0, JText::_('COM_REDFORM_WRONG_PARAMETERS_FOR_REDFORMCORE_GETSIDSANSWERS'));

				return false;
			}
		}
		else
		{
			$ids = implode(',', $sids);
		}

		// Get associated form id
		$query = $db->getQuery(true);

		$query->select('form_id')
			->from('#__rwf_submitters')
			->where('id IN (' . $ids . ')');
		$db->setQuery($query);
		$form_id = $db->loadResult();

		if (!$form_id)
		{
			Jerror::raiseWarning(0, JText::_('COM_REDFORM_No_submission_for_these_sids'));

			return false;
		}

		// Get data
		$query = $db->getQuery(true)
			->select('s.id as sid, f.*, s.price')
			->from('#__rwf_forms_' . $form_id . ' AS f')
			->join('INNER', '#__rwf_submitters AS s on s.answer_id = f.id')
			->where('s.id IN (' . $ids . ')');
		$db->setQuery($query);
		$submissionsData = $db->loadObjectList('sid');

		return $submissionsData;
	}

	/**
	 * Return submission(s) price(s) associated to a submit_key
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return array indexed by submitter_id
	 */
	public function getSubmissionPrice($submit_key)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('s.id, s.submit_key, s.price, s.currency');
		$query->from('#__rwf_submitters AS s');
		$query->join('INNER', '#__rwf_forms AS f ON f.id = s.form_id');
		$query->where('s.submit_key = ' . $db->q($submit_key));

		$db->setQuery($query);
		$res = $db->loadObjectList('s.id');

		return ($res);
	}
}
