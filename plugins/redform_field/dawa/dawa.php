<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  paymentnotificationemail
 *
 * @copyright   Copyright (C) 2008-2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

jimport('redevent.bootstrap');

RLoader::registerPrefix('PlgRedform_FieldEventacymailinglists', __DIR__);

/**
 * redFORM custom acymailing lists from redEVENT event
 *
 * @since  3.0
 */
class PlgRedform_FieldDawa extends JPlugin
{
	protected $autoloadLanguage = true;

	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'dawa';

	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An optional associative array of configuration settings.
	 *                            Recognized key values include 'name', 'group', 'params', 'language'
	 *                            (this list is not meant to be comprehensive).
	 */
	public function __construct($subject, array $config)
	{
		parent::__construct($subject, $config);

		RedeventBootstrap::bootstrap();
	}

	/**
	 * Add supported field type(s)
	 *
	 * @param   string[]  &$types  types
	 *
	 * @return void
	 */
	public function onRedformGetFieldTypes(&$types)
	{
		$types[] = 'dawa_address';
		$types[] = 'dawa_city';
		$types[] = 'dawa_zip';
	}

	/**
	 * Add supported field type(s) as option(s)
	 *
	 * @param   object[]  &$options  options
	 *
	 * @return void
	 */
	public function onRedformGetFieldTypesOptions(&$options)
	{
		$options[] = JHtml::_('select.option', 'dawa_address', JText::_('PLG_REDFORM_FIELD_DAWA_FIELD_DAWA_ADDRESS'));
		$options[] = JHtml::_('select.option', 'dawa_city', JText::_('PLG_REDFORM_FIELD_DAWA_FIELD_DAWA_CITY'));
		$options[] = JHtml::_('select.option', 'dawa_zip', JText::_('PLG_REDFORM_FIELD_DAWA_FIELD_DAWA_ZIP'));
	}

	/**
	 * Return an instance of supported types, if matches.
	 *
	 * @param   string     $type       type of field
	 * @param   RdfRfield  &$instance  instance of field
	 *
	 * @return void
	 */
	public function onRedformGetFieldInstance($type, &$instance)
	{
		switch ($type)
		{
			case 'dawa_address':
				$instance = new PlgRedform_FieldEventacymailinglistsFieldDawa_Address;
				$instance->setPluginParams($this->params);
				break;

			case 'dawa_city':
				$instance = new PlgRedform_FieldEventacymailinglistsFieldDawa_City;
				$instance->setPluginParams($this->params);
				break;

			case 'dawa_zip':
				$instance = new PlgRedform_FieldEventacymailinglistsFieldDawa_Zip;
				$instance->setPluginParams($this->params);
				break;
		}
	}
}
