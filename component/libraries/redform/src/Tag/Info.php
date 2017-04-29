<?php
/**
 * @package    Redform.Library
 *
 * @copyright  Copyright (C) 2009 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

namespace Redform\Tag;

defined('_JEXEC') or die;

/**
 * Tag container
 *
 * @package  Redform.Library
 * @since    __deploy_version__
 */
class Info
{
	public $name;

	public $description;

	public $section;

	// For custom and text library
	public $id = 0;

	/**
	 * Constructor
	 *
	 * @param   string  $name     name
	 * @param   string  $desc     description
	 * @param   string  $section  section
	 * @param   int     $id       id
	 */
	public function __construct($name, $desc, $section = 'General', $id = 0)
	{
		$name = trim($name);
		$this->name        = $name;
		$this->description = trim($desc);
		$this->section     = trim($section);
		$this->id          = $id;

		return $this;
	}
}
