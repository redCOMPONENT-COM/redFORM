<?php
/**
 * @package     Redform.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');
JHtml::_('behavior.formvalidator');

/**
 * redFORM Field
 *
 * @package     Redform.Library
 * @subpackage  Fields
 * @since       3.3.26
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
	 * @since 3.3.26
	 */
	protected function getInput()
	{
		$script = <<<JS
			jQuery(document).ready(function(){
				var checkExist = setInterval(function() {
					   if (document.formvalidator !== undefined) {
						   document.formvalidator.setHandler('timeinterval', function(value) {
							   regex=/^[0-9]+\s*(day[s]*|week[s]*|month[s]*|year[s]*)$/;
							   return regex.test(value);
							});
						  clearInterval(checkExist);
					   }
					}, 100);
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
	 * @since 3.3.26
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();
		$data['class'] = 'validate-timeinterval' . ($data['class'] ? ' ' : '') . $data['class'];

		return $data;
	}
}
