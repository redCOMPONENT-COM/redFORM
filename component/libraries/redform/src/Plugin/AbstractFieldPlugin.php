<?php
/**
 * @package    Redform.Library
 *
 * @copyright  Copyright (C) 2009 - 2017 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

namespace Redform\Plugin;

defined('_JEXEC') or die;

/**
 * Tag container
 *
 * @package  Redform.Library
 * @since    __deploy_version__
 */
abstract class AbstractFieldPlugin extends \JPlugin
{
	protected $autoloadLanguage = true;

	/**
	 * Add supported field type(s)
	 *
	 * @param   string[]  &$types  types
	 *
	 * @return void
	 */
	abstract public function onRedformGetFieldTypes(&$types);

	/**
	 * Add supported field type(s) as option(s)
	 *
	 * @param   object[]  &$options  options
	 *
	 * @return void
	 */
	abstract public function onRedformGetFieldTypesOptions(&$options);

	/**
	 * Return an instance of supported types, if matches.
	 *
	 * @param   string     $type       type of field
	 * @param   RdfRfield  &$instance  instance of field
	 *
	 * @return void
	 */
	abstract public function onRedformGetFieldInstance($type, &$instance);
}
