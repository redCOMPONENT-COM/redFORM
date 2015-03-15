<?php
/**
 * @package    Redform.front
 *
 * @copyright  Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Display Notification model
 *
 * @package  Redform.front
 * @since    3.0
 */
class RedformModelNotification extends RModel
{
	/**
	 * Return Notification text
	 *
	 * @param   string  $submitKey  submit key
	 *
	 * @return mixed
	 */
	public function getNotification($submitKey = null)
	{
		if (!$submitKey)
		{
			$submitKey = JFactory::getApplication()->input->get('submitKey');
		}

		$rdfCore = new RdfCore;
		$answers = $rdfCore->getAnswers($submitKey)->getSingleSubmission();
		$form = $rdfCore->getForm($answers->getFormId());

		return $answers->replaceTags($form->notificationtext);
	}
}
