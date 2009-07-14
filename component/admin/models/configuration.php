<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */
 
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport( 'joomla.application.component.model' );

/**
 * Order Model
 */
class RedformModelConfiguration extends JModel {
	/** @var integer Total entries */
	protected $_total = null;
	
	/** @var integer pagination limit starter */
	protected $_limitstart = null;
	
	/** @var integer pagination limit */
	protected $_limit = null;
	   
	/**
	 * Show all orders for which an invitation to fill in
	 * a testimonal has been sent
	 */
	function getConfiguration() {
		$db = JFactory::getDBO();
		/* Get all the orders based on the limits */
		$query = "SELECT name, value
				FROM #__rwf_configuration";
		$db->setQuery($query);
		$configuration = $db->loadObjectList('name');
		
		/* Check the configuration options */
		if (!isset($configuration['phplist_path'])) {
			$configuration['phplist_path']->name = 'phplist_path';
			$configuration['phplist_path']->value = JPATH_BASE.DS.'lists';
		}
		
		if (!isset($configuration['use_phplist'])) {
			$configuration['use_phplist']->name = 'use_phplist';
			$configuration['use_phplist']->value = 0;
		}
		
		if (!isset($configuration['use_ccnewsletter'])) {
			$configuration['use_ccnewsletter']->name = 'use_ccnewsletter';
			$configuration['use_ccnewsletter']->value = 0;
		}
		
		if (!isset($configuration['use_acajoom'])) {
			$configuration['use_acajoom']->name = 'use_acajoom';
			$configuration['use_acajoom']->value = 0;
		}
		
		if (!isset($configuration['filelist_path'])) {
			$configuration['filelist_path']->name = 'filelist_path';
			$configuration['filelist_path']->value = JPATH_ROOT.DS.'images';
		}
		
		return $configuration;
	}
	
	/**
	* Save the configuration
	*/
	function store() {
		global $mainframe;
		$db = JFactory::getDBO();
		
		$configuration = JRequest::getVar('configuration', false);
		$error = false;
		if (is_array($configuration)) {
			foreach ($configuration as $name => $value) {
				$query = "INSERT INTO #__rwf_configuration (name, value)
						VALUES ('".$name."', '".$db->getEscaped($value)."')
						ON DUPLICATE KEY UPDATE value = '".$db->getEscaped($value)."'";
				$db->setQuery($query);
				if (!$db->query()) {
					$mainframe->enqueueMessage(JText::_('There was a problem storing value').' '.$name, 'error');
					$error = true;
				}
			}
			if (!$error) $mainframe->enqueueMessage(JText::_('Configuration has been saved'));
		}
		else $mainframe->enqueueMessage(JText::_('Configuration could not be saved'));
	}
}
?>
