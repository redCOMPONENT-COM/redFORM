<?php
/**
 * @package     Redform.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

jimport('joomla.form.fields.list');

/**
 * Redform gateway select field
 *
 * @package     Redform.Library
 * @subpackage  Fields
 * @since       2.5
 */
class JFormFieldRedformgateway extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'redformgateway';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		JPluginHelper::importPlugin('redform_payment');
		$dispatcher = JDispatcher::getInstance();
		$gateways = array();
		$results = $dispatcher->trigger('onGetGateway', array(&$gateways));

		$options = array();

		if (count($gateways))
		{
			foreach ($gateways as $g)
			{
				if (isset($g['backendlabel']) && $g['backendlabel'])
				{
					$label = $g['backendlabel'];
				}
				elseif (isset($g['label']) && $g['label'])
				{
					$label = $g['label'];
				}
				else
				{
					$label = $g['name'];
				}

				$options[] = JHtml::_('select.option', $g['name'], $label);
			}
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$text = parent::getInput();

		// We add an hidden field just in case nothing is selected, as in this case the field is not posted !
		$sav = $this->multiple;
		$this->multiple = false;
		$text .= '<input type="hidden" name="' . $this->getName($this->fieldname . '_present') . '" value="1" />';
		$this->multiple = $sav;

		return $text;
	}
}
