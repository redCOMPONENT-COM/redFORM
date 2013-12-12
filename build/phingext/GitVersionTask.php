<?php
/**
 * @package    Redphing.builder
 * @copyright  Copyright (c) 2013 redCOMPONENT
 * @license    GNU GPL version 2 or, at your option, any later version
 */

require_once 'phing/Task.php';

/**
 * Git latest commit to Phing property
 *
 * @package    Redphing.builder
 * @copyright  Copyright (c) 2013 redCOMPONENT
 * @license    GNU GPL version 2 or, at your option, any later version
 * @since      2.5
 */
class GitVersionTask extends Task
{
	private $propertyName = "git.version";

	/**
	 * Sets the name of the property to use
	 *
	 * @param   string  $propertyName  property name
	 *
	 * @return void
	 */
	function setPropertyName($propertyName)
	{
		$this->propertyName = $propertyName;
	}

	/**
	 * Returns the name of the property to use
	 *
	 * @return string
	 */
	function getPropertyName()
	{
		return $this->propertyName;
	}

	/**
	 * The main entry point
	 *
	 * @return void
	 *
	 * @throws BuildException
	 */
	function main()
	{
		exec('git describe ', $out);
		$this->project->setProperty($this->getPropertyName(), trim($out[0]));
	}
}
