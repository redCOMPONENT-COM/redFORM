<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license GNU/GPL, see LICENSE.php
 * redFORM can be downloaded from www.redcomponent.com
 * redFORM is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redFORM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redFORM; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Installation file
 */

/* ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );


function upgradeFormColumns()
{
  $db = JFactory::getDBO();
	
  /** Migration of rwf_form_x tables => the column name must be changed from 'fieldname' to 'field'+'field_id' **/
  //first get all forms
  $query = ' SELECT id FROM #__rwf_forms ';
  $db->setQuery($query);
  $form_ids = $db->loadResultArray();
  
  if (!empty($form_ids))
  {
    $need_upgrade = array();
    // first a quick check to see if we already upgraded
    $q = " SHOW COLUMNS FROM " . $db->nameQuote('#__rwf_forms_'. $form_ids[0]);
    $db->setQuery($q);
    $cols = $db->loadObjectList();
    
    foreach ($cols as $col)
    {
      if ($col->Field == 'id') {
        continue;
      }
      if (!preg_match('/^field_[0-9]+$/', $col->Field)) {
        $need_upgrade[] = $col->Field;
      }
    }
    
    if (!empty($need_upgrade))
    {
      foreach ($form_ids as $form_id)
      {
        $need_upgrade = array();
        $q = " SHOW COLUMNS FROM " . $db->nameQuote('#__rwf_forms_'. $form_id);
		    $db->setQuery($q);
		    $cols = $db->loadObjectList();
		    
		    foreach ($cols as $col)
		    {
		      if ($col->Field == 'id') {
		        continue;
		      }
		      if (!preg_match('/^field_[0-9]+$/', $col->Field)) {
		        $need_upgrade[] = $col->Field;
		      }
		    }
    
		    if (empty($need_upgrade)) {
		    	continue;
		    }
        echo '#__rwf_forms_'. $form_id .' '. 'NEEDS UPGRADE => '. implode(', ', $need_upgrade) .'<br/>';
        // backup the table
        $query = ' CREATE TABLE '. $db->nameQuote('#__rwf_forms_'. $form_id .'_bak_b39'.'_'.time())
               . ' SELECT * FROM '. $db->nameQuote('#__rwf_forms_'. $form_id)
               ;
        $db->setQuery($query);
        $db->query();
        
        // get fields from fields table
        $query = ' SELECT id, field FROM #__rwf_fields WHERE form_id = '. $db->quote($form_id);
        $db->setQuery($query);
        $fields = $db->loadObjectList();
        
        $replaced = array();
        foreach ($fields as $field)
        {
          $colname = str_replace(' ', '', strtolower($field->field));
          if (in_array($colname, $need_upgrade))
          {
          	if (strstr($colname, '.')) {
          		$quotedcol = '`' . $colname . '`';
          	}
          	else {
              $quotedcol = $db->nameQuote($colname);          		
          	}
          	$query = ' ALTER TABLE '. $db->nameQuote('#__rwf_forms_'. $form_id)
          	       . ' CHANGE '. $quotedcol .' '. $db->nameQuote('field_'. $field->id) . ' TEXT'
          	       ;
	          $db->setQuery($query);
	          if (!$db->query()) {
	          	// try to force mysql style quoting (if there are points in field name)
	          	echo $db->getErrorMsg() . '<br/>';    
	          }
	          else {
	          	$replaced[] = $colname;
	          }
	        }
        }
        
        if (count($replaced) != count($need_upgrade)) {
            echo JText::_('COM_REDFORM_ERROR_NOT_ALL_COLUMNS_COULD_BE_MATCHED_AND_REPLACED') . ': '. implode(', ', array_diff($need_upgrade, $replaced)) . '<br/>';        	
        }        
      }
    }
  }
}

function com_install() 
{
	$db = JFactory::getDBO();	
  
	/**
	 * get tables details
	 */
	$tables = array( '#__rwf_configuration', 
	                 '#__rwf_fields',
	                 '#__rwf_forms',
	                 '#__rwf_submitters',
	                 '#__rwf_values',
	                 '#__rwf_mailinglists',
	                 '#__rwf_payment',
	               );
	$tables = $db->getTableFields($tables, false);
	
	/* Get the current columns */
	$cols = $tables['#__rwf_fields'];
	
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
	
	/* Check if we have the tooltip column */
	if (!array_key_exists('readonly', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_fields ADD COLUMN ".$db->nameQuote('readonly')." TINYINT(1) NOT NULL DEFAULT 0";
		$db->setQuery($q);
		$db->query();
	}
	
	if (!array_key_exists('default', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_fields ADD COLUMN `default` varchar(255) DEFAULT NULL";
		$db->setQuery($q);
		$db->query();
	}

	/* Check if we have the redmember_field column */
	if (!array_key_exists('redmember_field', $cols)) {
		$q = "ALTER IGNORE TABLE `#__rwf_fields` ADD `redmember_field` varchar(20) NULL DEFAULT NULL AFTER `field` ";
		$db->setQuery($q);
		$db->query();
	}

	/* Check if we have the redmember_field column */
	if (!array_key_exists('field_header', $cols)) {
		$q = "ALTER IGNORE TABLE `#__rwf_fields` ADD `field_header` varchar(255) NOT NULL AFTER `field` ";
		$db->setQuery($q);
		$db->query();
	}

	/* Check if we have the fieldtype column */
	if (!array_key_exists('fieldtype', $cols)) 
	{
		$q = "ALTER IGNORE TABLE `#__rwf_fields` ADD `fieldtype` varchar(30) NOT NULL DEFAULT '' AFTER `field` ";
		$db->setQuery($q);
		if ($db->query()) 
		{
			$q = "UPDATE #__rwf_fields AS f, jos_rwf_values AS v SET f.fieldtype = v.fieldtype WHERE f.id = v.field_id";
			$db->setQuery($q);
			if ($db->query()) 
			{
				$q = "ALTER IGNORE TABLE `#__rwf_values` DROP `fieldtype` ";
				$q = "UPDATE #__rwf_fields AS f, jos_rwf_values AS v SET f.fieldtype = v.fieldtype WHERE f.id = v.field_id";
				$db->setQuery($q);
				if ($db->query()) 
				{
				
				}
			}
		}
			
	}

	/* Check if we have the params column */
	if (!array_key_exists('params', $cols)) {
		$q = "ALTER IGNORE TABLE `#__rwf_fields` ADD `params` text NULL DEFAULT NULL ";
		$db->setQuery($q);
		$db->query();
	}
	
  /** add indexes **/
  if (empty($cols['form_id']->Key)) 
  {
    $q = "ALTER TABLE `#__rwf_fields` ADD INDEX (`form_id`)";
    $db->setQuery($q);
    $db->query();  	
  }
	
	/***************************************************************************************************************/
	/* Get the current columns */
	$cols = $tables['#__rwf_forms'];
	
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
	
	/* Check if we have the activatepayment column */
	if (!array_key_exists('activatepayment', $cols)) {
		$q = "ALTER IGNORE TABLE `#__rwf_forms` ADD COLUMN ".$db->nameQuote('activatepayment')." TINYINT(2) NOT NULL DEFAULT '0'";
		$db->setQuery($q);
		$db->query();
		$q = "ALTER IGNORE TABLE `#__rwf_forms` ADD COLUMN ".$db->nameQuote('currency')." VARCHAR(3) DEFAULT NULL";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the activatepayment column */
	if (!array_key_exists('paymentprocessing', $cols)) {
		$q = "ALTER IGNORE TABLE `#__rwf_forms` ADD COLUMN ".$db->nameQuote('paymentprocessing')." TEXT DEFAULT NULL";
		$db->setQuery($q);
		$db->query();
		$q = "ALTER IGNORE TABLE `#__rwf_forms` ADD COLUMN ".$db->nameQuote('paymentaccepted')." TEXT DEFAULT NULL";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the notification payment email columns */
	if (!array_key_exists('contactpaymentnotificationsubject', $cols)) {
		$q = "ALTER IGNORE TABLE `#__rwf_forms` ADD COLUMN ".$db->nameQuote('contactpaymentnotificationsubject')." TEXT DEFAULT NULL";
		$db->setQuery($q);
		$db->query();
		$q = "ALTER IGNORE TABLE `#__rwf_forms` ADD COLUMN ".$db->nameQuote('contactpaymentnotificationbody')." TEXT DEFAULT NULL";
		$db->setQuery($q);
		$db->query();
		$q = "ALTER IGNORE TABLE `#__rwf_forms` ADD COLUMN ".$db->nameQuote('submitterpaymentnotificationsubject')." TEXT DEFAULT NULL";
		$db->setQuery($q);
		$db->query();
		$q = "ALTER IGNORE TABLE `#__rwf_forms` ADD COLUMN ".$db->nameQuote('submitterpaymentnotificationbody')." TEXT DEFAULT NULL";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the redirect column */
	if (!array_key_exists('redirect', $cols)) {
		$q = "ALTER TABLE `#__rwf_forms` ADD `redirect` VARCHAR( 300 ) NULL DEFAULT NULL ";
		$db->setQuery($q);
		$db->query();
	}
	
	/* Check if we have the show_js_price column */
	if (!array_key_exists('show_js_price', $cols)) {
		$q = "ALTER TABLE `#__rwf_forms` ADD `show_js_price` tinyint(2) NOT NULL DEFAULT '1' ";
		$db->setQuery($q);
		$db->query();
	}
	
  /** add indexes **/
  if (empty($cols['vmproductid']->Key)) 
  {
    $q = "ALTER TABLE `#__rwf_forms` ADD INDEX (`vmproductid`)";
    $db->setQuery($q);
    $db->query();  	
  }
		
	/***************************************************************************************************************/
	/* Get the current columns */
	$cols = $tables['#__rwf_submitters'];
	
	if (array_key_exists('event_id', $cols)) {
		$q = "ALTER TABLE `#__rwf_submitters` CHANGE `event_id` `xref` INT( 11 ) NULL DEFAULT NULL";
		$db->setQuery($q);
		$db->query();
	}
	
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
	if (!array_key_exists('submit_key', $cols)) 
	{
		$q = "ALTER IGNORE TABLE #__rwf_submitters ADD COLUMN `submit_key` varchar(45) NOT NULL";
		$db->setQuery($q);
		$db->query();
				
		$q = "UPDATE #__rwf_submitters set `submit_key` = MD5(id)";
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

	/* Check if we have the price column */
	if (!array_key_exists('price', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_submitters ADD COLUMN `price` double NULL DEFAULT NULL";
		$db->setQuery($q);
		$db->query();
	}

	/* Check if we have the price column */
	if (!array_key_exists('integration', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_submitters ADD COLUMN `integration` VARCHAR(30) NULL DEFAULT NULL";
		$db->setQuery($q);
		$db->query();
	}
	
  /** add indexes **/
  if (empty($cols['form_id']->Key)) 
  {
    $q = "ALTER TABLE `#__rwf_submitters` ADD INDEX (`form_id`)";
    $db->setQuery($q);
    $db->query();  	
  }
  if (empty($cols['answer_id']->Key)) 
  {
    $q = "ALTER TABLE `#__rwf_submitters` ADD INDEX (`answer_id`)";
    $db->setQuery($q);
    $db->query();  	
  }
	
	/***************************************************************************************************************/
	/* Get the current columns */
	$cols = $tables['#__rwf_values'];
  
  if (!stristr($cols['value']->Type, 'text')) {
    $q = "ALTER TABLE `#__rwf_values` CHANGE `value` `value` TEXT NULL DEFAULT NULL";
    $db->setQuery($q);
    $db->query();
  }
  
	/* Check if we have the price column */
	if (!array_key_exists('price', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_values ADD COLUMN `price` double NULL DEFAULT NULL";
		$db->setQuery($q);
		$db->query();
	}
    
	if (!array_key_exists('label', $cols)) {
		$q = "ALTER IGNORE TABLE #__rwf_values ADD COLUMN `label` varchar(255) NOT NULL default ''";
		$db->setQuery($q);
		$db->query();
		
		$q = "UPDATE #__rwf_values SET label = value";
		$db->setQuery($q);
		$db->query();
	}
	
  /** add indexes **/
  if (empty($cols['field_id']->Key)) 
  {
    $q = "ALTER TABLE `#__rwf_values` ADD INDEX (`field_id`)";
    $db->setQuery($q);
    $db->query();  	
  }
	
	/***************************************************************************************************************/
	/* Get the current columns */
	$cols = $tables['#__rwf_mailinglists'];
  
	/* Check if we have the field_id column */
	if (!array_key_exists('field_id', $cols)) 
	{
		$q = "ALTER IGNORE TABLE #__rwf_mailinglists CHANGE `id` `field_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0'";
		$db->setQuery($q);
		$db->query();
		
		// id used to track value_id, so we need to replace with corresponding field_id
		$q = ' UPDATE `jos_rwf_mailinglists` AS m, jos_rwf_values AS v '
		   . ' SET m.field_id = v.field_id '
		   . ' WHERE m.field_id = v.id ';
		$db->setQuery($q);
		if (!$db->query()) {
			JError::raiseWarning(0, 'Conversion of mailing list reference from value_id to field_id failed - please manually edit each email field type');
		}
	}
	
	/***************************************************************************************************************/
  // new structure for rwf_forms_x tables
  upgradeFormColumns();
	
  /** remove previous instances of the plugin, if there are more than one **/
  $query = ' SELECT COUNT(*) FROM #__extensions WHERE name = '. $db->Quote('Content - redFORM');
  $db->setQuery($query);
  $nb_plug = $db->loadResult();  
  
  if ($nb_plug && $nb_plug > 1) {
    $query = ' DELETE FROM #__extensions WHERE name = '. $db->Quote('Content - redFORM');
    $db->setQuery($query);
    if ($db->query()) {
      echo JText::_('COM_REDFORM_Removed_ghost_instances_of_redFORM_content_plugin').'<br />';
    }
  }
  
	/* Install plugin */
	jimport('joomla.filesystem.file');
	jimport('joomla.filesystem.folder');	
	JFolder::copy(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_redform'.DS.'plugins'.DS.'content_redform', JPATH_SITE.DS.'tmp'.DS.'redform_plugin', '', true);
	JFile::move(JPATH_SITE.DS.'tmp'.DS.'redform_plugin'.DS.'redform.xm', JPATH_SITE.DS.'tmp'.DS.'redform_plugin'.DS.'redform.xml');
	$installer = new JInstaller();
	$installer->setAdapter('plugin');
	if (!$installer->install(JPATH_SITE.DS.'tmp'.DS.'redform_plugin')) {
	  echo JText::_('COM_REDFORM_Plugin_install_failed:_') . $installer->getError().'<br />';
	}
	else {
	  // autopublish the plugin
	  $query = ' UPDATE #__plugins SET published = 1 WHERE name = '. $db->Quote('Content - redFORM');
    $db->setQuery($query);
    if ($db->query()) {
	    echo JText::_('COM_REDFORM_Succesfully_installed_redform_content_plugin').'<br />';
    }
    else {
      echo JText::_('COM_REDFORM_Error_publishing_redform_content_plugin').'<br />';      
    }
	  
	}
}
?>