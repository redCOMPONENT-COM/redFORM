<?php
/**
 * @package     Redform.Plugin
 * @subpackage  Redform.economic
 *
 * @copyright   Copyright (C) 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * This plugin makes all billing form fields required
 *
 * @since  3.0
 */
class plgRedformNikkbbilling extends JPlugin
{
	/**
	 * process form
	 *
	 * @param   RForm  $form  form
	 * @param   array  $data  data
	 *
	 * @return void
	 */
	public function onContentPrepareForm(RForm $form, $data)
	{
		if (!$form->getName() == 'com_redform.edit.billing.billing')
		{
			return;
		}

		$form->setFieldAttribute('fullname', 'required', true);
		$form->setFieldAttribute('company', 'required', true);
		$form->setFieldAttribute('address', 'required', true);
		$form->setFieldAttribute('city', 'required', true);
		$form->setFieldAttribute('zipcode', 'required', true);
		$form->setFieldAttribute('phone', 'required', true);
		$form->setFieldAttribute('email', 'required', true);
		$form->setFieldAttribute('country', 'required', true);
	}
}
