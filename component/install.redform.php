<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 *
 * Installation file
 */

/* ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

function com_install() {
	$db = JFactory::getDBO();
	
	/* Get the current columns */
	$q = "SHOW COLUMNS FROM #__rwf_fields";
	$db->setQuery($q);
	$cols = $db->loadObjectList('Field');
	
	/* Check if we have the validate column */
	if (!array_key_exists('validate', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_fields ADD COLUMN ".$db->nameQuote('validate')." TINYINT(1) NOT NULL DEFAULT 0";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the unique column */
	if (!array_key_exists('unique', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_fields ADD COLUMN ".$db->nameQuote('unique')." TINYINT(1) NOT NULL DEFAULT 0";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the tooltip column */
	if (!array_key_exists('tooltip', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_fields ADD COLUMN ".$db->nameQuote('tooltip')." VARCHAR(255) DEFAULT NULL";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Get the current columns */
	$q = "SHOW COLUMNS FROM #__rwf_forms";
	$db->setQuery($q);
	$cols = $db->loadObjectList('Field');
	
	/* Check if we have the validate column */
	if (!array_key_exists('virtuemartactive', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_forms ADD COLUMN ".$db->nameQuote('virtuemartactive')." TINYINT(1) NOT NULL DEFAULT 0";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the unique column */
	if (!array_key_exists('vmproductid', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_forms ADD COLUMN ".$db->nameQuote('vmproductid')." INT(1) default NULL";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the unique column */
	if (!array_key_exists('vmitemid', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_forms ADD COLUMN ".$db->nameQuote('vmitemid')." INT(4) NOT NULL DEFAULT 1";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the captchaactive column */
	if (!array_key_exists('captchaactive', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_forms ADD COLUMN ".$db->nameQuote('captchaactive')." TINYINT(1) NOT NULL DEFAULT 0";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the captchaactive column */
	if (!array_key_exists('access', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_forms ADD COLUMN ".$db->nameQuote('access')." TINYINT(3) UNSIGNED DEFAULT '0' NOT NULL";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the access column */
	if (!array_key_exists('access', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_forms ADD COLUMN ".$db->nameQuote('access')." TINYINT(3) UNSIGNED DEFAULT '0' NOT NULL";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the mailinglist active column */
	if (array_key_exists('mailinglistactive', $cols)) {
		$q = "ALTER IGNORE TABLE `#__rwf_forms` DROP `mailinglistactive` ";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the mailinglist active column */
	if (array_key_exists('mailinglistname', $cols)) {
		$q = "ALTER IGNORE TABLE `#__rwf_forms` DROP `mailinglistname` ";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Get the current columns */
	$q = "SHOW COLUMNS FROM #__rwf_submitters";
	$db->setQuery($q);
	$cols = $db->loadObjectList('Field');
	if (array_key_exists('event_id', $cols)) $upgrade = true;
	else $upgrade = false;
	
  	/* Check if we have the answer_id column */
	if (!array_key_exists('answer_id', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_submitters ADD COLUMN ".$db->nameQuote('answer_id')." INT(11) NOT NULL DEFAULT 0";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the submitternewsletter column */
	if (!array_key_exists('submitternewsletter', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_submitters ADD COLUMN ".$db->nameQuote('submitternewsletter')." INT(11) NOT NULL DEFAULT 0";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the rawformdata column */
	if (!array_key_exists('rawformdata', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_submitters ADD COLUMN ".$db->nameQuote('rawformdata')." text NOT NULL";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the submit_key column */
	if (!array_key_exists('submit_key', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_submitters ADD COLUMN `submit_key` varchar(45) NOT NULL";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the waiting column */
	if (array_key_exists('waiting', $cols) && !array_key_exists('waitinglist', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_submitters CHANGE `waiting` `waitinglist` tinyint(1) NOT NULL default '0'";
		$db->setQuery($q);
		$db->query();
	}
	else if (!array_key_exists('waitinglist', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_submitters ADD COLUMN `waitinglist` tinyint(1) NOT NULL default '0'";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the confirmed column */
	if (!array_key_exists('confirmed', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_submitters ADD COLUMN `confirmed` tinyint(1) NOT NULL default '0'";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the confirmdate column */
	if (!array_key_exists('confirmdate', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_submitters ADD COLUMN `confirmdate` datetime default NULL";
		$db->setQuery($q);
		$db->query();
	}
	
	 /* Get the current columns */
  $q = "SHOW COLUMNS FROM #__rwf_values";
  $db->setQuery($q);
  $cols = $db->loadObjectList('Field');
  
  if (!stristr($cols['value']->Type, 'text')) {
    $q = "ALTER TABLE `#__rwf_values` CHANGE `value` `value` TEXT NULL DEFAULT NULL";
    $db->setQuery($q);
    $db->query();
  }
	
if ($upgrade) {
	/* The event values need to be updated with the equivalent xref */
	$q = "SELECT id, event_id FROM #__rwf_submitters";
	$db->setQuery($q);
	$events = $db->loadObjectList();
	if (is_array($events)) {
		foreach ($events as $key => $event) {
			/* Get the xref value */
			$q = "SELECT id FROM #__redevent_event_venue_xref WHERE eventid = ".$event->event_id;
			$db->setQuery($q);
			$xref = $db->loadResult();
			
			/* Update the submitters table */
			$q = "UPDATE #__rwf_submitters SET event_id = ".$xref." WHERE id = ".$event->id;
			$db->setQuery($q);
			$db->query();
		}
		
		/* The event becomes xref */
		$q = "ALTER TABLE `#__rwf_submitters` CHANGE `event_id` `xref` INT( 11 ) NULL DEFAULT NULL";
		$db->setQuery($q);
		$db->query();
		
		/* Fill the new columns with data */
		$q = "UPDATE `#__rwf_submitters` s 
			LEFT JOIN #__redevent_register_bak r
			ON s.answer_id = r.submitter_id
			SET s.submit_key = r.submitter_id, 
			s.waiting = r.waiting, 
			s.confirmed = r.confirmed, 
			s.confirmdate = r.confirmdate";
		$db->setQuery($q);
		$db->query();
	}
}
	/* Install plugin */
	jimport('joomla.filesystem.file');
	jimport('joomla.filesystem.folder');
	/* 1. XML file */
	if(!JFile::copy(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_redform'.DS.'plugins'.DS.'redform.xm', JPATH_SITE.DS.'plugins'.DS.'content'.DS.'redform.xml')){
		echo JText::_('<b>Failed</b> to copy plugin xml file<br />');
	}
	/* 2. PHP file */
	if(!JFile::copy(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_redform'.DS.'plugins'.DS.'redform.php', JPATH_SITE.DS.'plugins'.DS.'content'.DS.'redform.php')){
		echo JText::_('<b>Failed</b> to copy plugin php file<br />');
	}
	
	/* 3. Language files */
	$langfiles = JFolder::files(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_redform'.DS.'plugins'.DS.'language');
	$basefolder = JPATH_SITE.DS.'language';
	foreach ($langfiles as $key => $langfile) {
		$lang = substr($langfile, 0, 5);
		if (JFolder::exists($basefolder.DS.$lang)) {
			if(!JFile::copy(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_redform'.DS.'plugins'.DS.'language'.DS.$langfile, $basefolder.DS.$lang.DS.$langfile)) {
				echo JText::_('<b>Failed</b> to copy language file: '.$langfile.'<br />');
			}
		}
	}
	
	
	/* Store the plugin settings */
	$plugin = JTable::getInstance( 'plugin' );
	$plugin->name = 'Content - redFORM';
	$plugin->element = 'redform';
	$plugin->folder = 'content';
	$plugin->ordering = 1;
	$plugin->published = 1;
	
	if (!$plugin->store()) {
		echo JText::_('Plugin install failed:') .$plugin->getError().'<br />';
	}
}
?>