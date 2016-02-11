<?php
/**
 * @package     Redform.Plugin
 * @subpackage  Redform.prefillredirect
 *
 * @copyright   Copyright (C) 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * Set form session data and redirect to article
 *
 * @package     Redform.Plugin
 * @subpackage  Redform.prefillredirect
 * @since       3.0
 */
class plgRedformPrefillredirect extends JPlugin
{
	/**
	 * constructor
	 *
	 * @param   object  $subject  subject
	 * @param   array   $params   params
	 */
	public function __construct($subject, $params)
	{
		parent::__construct($subject, $params);
		$this->loadLanguage();
	}

	public function onAjaxRedformprefill()
	{
		$app = JFactory::getApplication();

		$fields = $app->input->get('fields', null, 'array');
		$formId = $app->input->getInt('formId');
		$return = $app->input->getString('return');

		$app->setUserState('formdata' . $formId, array((object) $fields));
		$app->redirect($return);
	}
}
