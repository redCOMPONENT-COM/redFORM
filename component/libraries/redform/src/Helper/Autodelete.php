<?php
/**
 * @package    Redform.Library
 *
 * @copyright  Copyright (C) 2009 - 2018 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

namespace Redform\Helper;

defined('_JEXEC') or die;

/**
 * Autodelete helper
 *
 * @package  Redform.Library
 * @since    __deploy_version__
 */
class Autodelete
{
	const USE_GLOBAL = -2;
	const DISABLED = 0;
	const CUSTOM = -1;

	/**
	 * Process submissions
	 *
	 * @var   integer[]   $formIds  form ids
	 *
	 * @return void
	 * @since  __deploy_version__
	 */
	public function process($formIds = null)
	{
		$forms = $this->getForms($formIds);

		foreach ($forms as $form)
		{
			$interval = $this->getInterval($form);

			if (!$interval)
			{
				continue;
			}

			$date = \JFactory::getDate('- ' . $interval);

			$db = \JFactory::getDbo();

			$query = 'DELETE s.*, f.*'
				. ' FROM #__rwf_submitters AS s'
				. ' LEFT JOIN #__rwf_forms_' . $form->id . ' AS f ON f.id = s.answer_id'
				. ' WHERE s.form_id = ' . $form->id
				. ' AND LENGTH(s.integration) = 0'
				. ' AND s.submission_date < ' . $db->q($date->format('Y-m-d'));

			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Get all forms
	 *
	 * @var   integer[]   $formIds  form ids
	 *
	 * @return \RdfEntityForm[]
	 */
	private function getForms($formIds = null)
	{
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__rwf_forms');

		if (!empty($formIds))
		{
			$query->where('id IN (' . implode(', ', $formIds) . ')');
		}

		$db->setQuery($query);
		$res = $db->loadObjectList();

		return array_map(
			function ($row)
			{
				$entity = \RdfEntityForm::getInstance($row->id);
				$entity->bind($row);

				return $entity;
			},
			$res
		);
	}

	/**
	 * Return interval, or false if disabled
	 *
	 * @return int|mixed
	 */
	private function getInterval(\RdfEntityForm $form)
	{
		$formParams = $form->params;

		if ($formParams->get('auto_delete', self::USE_GLOBAL) == self::USE_GLOBAL)
		{
			return $this->getGlobalInterval();
		}

		if ($formParams->get('auto_delete', self::DISABLED) == self::DISABLED)
		{
			return false;
		}

		if ($formParams->get('auto_delete', self::DISABLED) == self::CUSTOM)
		{
			return $formParams->get('auto_delete_custom');
		}

		return $formParams->get('auto_delete', self::DISABLED);
	}

	/**
	 * Return global interval, or false if disabled
	 *
	 * @return int|mixed
	 */
	private function getGlobalInterval()
	{
		$settings = \RdfHelper::getConfig();

		if ($settings->get('auto_delete', self::DISABLED) == self::DISABLED)
		{
			return false;
		}

		if ($settings->get('auto_delete', self::DISABLED) == self::CUSTOM)
		{
			return $settings->get('auto_delete_custom');
		}

		return $settings->get('auto_delete', self::DISABLED);
	}
}