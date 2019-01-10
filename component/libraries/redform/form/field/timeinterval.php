<?php
/**
 * @package     Redform.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * redFORM Field
 *
 * @package     Redform.Library
 * @subpackage  Fields
 * @since       __deploy_version__
 */
class RedformFormFieldTimeinterval extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Timeinterval';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since __deploy_version__
	 */
	protected function getInput()
	{
		$script = <<<JS
			jQuery(document).ready(function(){
			   document.formvalidator.setHandler('timeinterval', function(value) {
			      regex=/^[0-9]+\s*(day[s]*|week[s]*|month[s]*|year[s]*)$/;
			      return regex.test(value);
			   });
			});
JS;
		JFactory::getDocument()->addScriptDeclaration($script);

		return parent::getInput();
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since __deploy_version__
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();
		$data['class'] = 'validate-timeinterval' . ($data['class'] ? ' ' : '') . $data['class'];

		return $data;
	}
}
