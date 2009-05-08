<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */

/* No direct access */
defined('_JEXEC') or die('Restricted access');

/**
 */
class TableConfiguration extends JTable {
	/** @var int Primary key */
	var $id = null;
	/** @var string The name of the configuration option */
	var $name = null;
	/** @var string The value of the configuration option */
	var $value = null;
	
	/**
	* @param database A database connector object
	*/
	function __construct( &$db ) {
		parent::__construct('#__rwf_configuration', 'id', $db );
	}
}
?>