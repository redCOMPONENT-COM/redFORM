<?php
/**
 * @package     Redform.Plugin
 * @subpackage  Redform.aesirmember
 *
 * @copyright   Copyright (C) 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * plugin class
 *
 * @since       3.0
 */
class plgRedformAesirmember extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since __deploy_version__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Add aesir field integration
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 *
	 * @return boolean
	 *
	 * @since __deploy_version__
	 */
	public function onContentPrepareForm(JForm $form, $data)
	{
		if ('com_redform.edit.field.field' != $form->getName())
		{
			return true;
		}

		$field = simplexml_load_file(__DIR__ . '/field.xml');

		$form->setField($field, 'params');
	}

	public function onRedformFieldLookupDefaultValue(RdfRfield $field, &$default)
	{
		if (!$customfieldId = $field->getParam('aesir_field'))
		{
			return true;
		}

		if (!$field->user)
		{
			return true;
		}

		$user = ReditemEntityMember::getInstance();
		$user->loadFromUser($field->user->id);

		if (!$user->isValid())
		{
			return true;
		}

		$customfield = ReditemEntityField::load($customfieldId);

		$default = $user->getFieldValue($customfield->fieldcode);
	}
}
