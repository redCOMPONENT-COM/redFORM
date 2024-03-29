<?php
/**
 * @package     Redform.plugins
 * @subpackage  mailing
 *
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Redform\Plugin\AbstractFieldPlugin;

defined('_JEXEC') or die( 'Restricted access');

require_once __DIR__ . '/field/daterange.php';

/**
 * Class plgRedform_fieldDaterange
 *
 * @since       3.3.23
 */
class plgRedform_fieldDaterange extends AbstractFieldPlugin
{
	/**
	 * Add supported field type(s)
	 *
	 * @param   string[]  &$types  types
	 *
	 * @return void
	 */
	public function onRedformGetFieldTypes(&$types)
	{
		$types[] = 'daterange';
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
		$options[] = \JHtml::_('select.option', 'daterange', JText::_('PLG_REDFORM_FIELD_DATERANGE_FIELD_DATERANGE'));
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
		if ('daterange' === $type)
		{
			$instance = new RdfRfieldDaterange;
			$instance->setPluginParams($this->params);
		}
	}
}
