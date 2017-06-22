<?php
/**
 * @package     Redform
 * @subpackage  mod_redform_latest_submissions
 *
 * @copyright   Copyright (C) 2008 - 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Module helper class
 *
 * @since       __deploy_version__
 */
class ModRedformLatestSubmissionHelper
{
	/**
	 * Return rows
	 *
	 * @param   JRegistry  $params  module params
	 *
	 * @return mixed
	 *
	 * @since __deploy_version__
	 */
	public static function getList($params)
	{
		$model = RModel::getAdminInstance('Submitters', array('ignore_request' => true), 'com_redform');

		$model->setState('list.ordering', 's.submission_date');
		$model->setState('list.direction', 'desc');
		$model->setState('list.limit', $params->get('limit', 10));

		return $model->getItems();
	}
}
