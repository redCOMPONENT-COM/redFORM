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
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport('joomla.application.component.controller');

/**
 * redFORM Controller
 */
class RedformControllerSubmitters extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();
		
		/* Redirect templates to templates as this is the standard call */
		$this->registerTask('save', 'apply');
		$this->registerTask('add',  'edit');
		$this->registerTask('forcedelete',  'remove');
	}
	
	function remove()
	{		
    $cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );

    if (!is_array( $cid ) || count( $cid ) < 1) {
      JError::raiseError(500, JText::_('COM_REDFORM_Select_an_item_to_delete' ) );
    }

    $model = $this->getModel('submitters');

    if (JRequest::getVar('task') == 'forcedelete') {
    	$msg = $model->delete($cid, true);
    }
    else {
    	$msg = $model->delete($cid);    	
    }

    $cache = &JFactory::getCache('com_redform');
    $cache->clean();

    $form_id = JRequest::getVar('form_id', 0);
    
    $this->setRedirect( 'index.php?option=com_redform&view=submitters' . ($form_id ? '&form_id='.$form_id : ''), $msg );
	}
	
  /**
   * logic for cancel an action
   *
   * @access public
   * @return void
   * @since 0.9
   */
  function cancel()
  {
    // Check for request forgeries
    // JRequest::checkToken() or die( 'Invalid Token' );
    $this->setRedirect( 'index.php?option=com_redform&view=submitters' );
  }
	
	/**
	 * Submitters
	 */
	function Submitters() {
    JRequest::setVar( 'view', 'submitters' );
    parent::display();
	}
	
	/**
	 * Export submitters data
	 */
	function Export() {
		$view = $this->getView('submitters', 'raw');
		$view->setModel( $this->getModel( 'submitters', 'RedformModel' ), true );
		$view->setLayout('submitters_export');
		$view->display();
	}
	

  /**
   * logic to create the edit event screen
   *
   * @access public
   * @return void
   * @since 0.9
   */
  function edit( )
  {
    JRequest::setVar( 'view', 'submitter' );
    JRequest::setVar( 'hidemainmenu', 1 );

    parent::display();
  }
	
	
	/**
	 * Redirect back to redEVENT
	 */
	public function RedEvent() {
		$mainframe = &JFactory::getApplication();
		$mainframe->redirect('index.php?option=com_redevent&view=attendees&xref='.JRequest::getInt('xref'));
	}
	
	function save()
	{		
    $form_id = JRequest::getVar('form_id', 0);
    $xref = JRequest::getVar('xref', 0);
    $integration = JRequest::getVar('integration', '');
    
    $rfcore = new RedFormCore();
    $res = $rfcore->saveAnswers($integration);
    
    if ($res) {
    	$msg = JText::_('COM_REDFORM_Submission_updated');
    	$type = 'message';
    }    
    else {
    	$msg = JText::_('COM_REDFORM_Submission_update_failed');   
    	$type = 'error'; 	
    }
    $url = 'index.php?option=com_redform&controller=submitters&task=submitters';
    if ($form_id) {
    	$url .= '&form_id='.$form_id;
    }
    if ($integration) {
    	$url .= '&integration='.$integration;
    }
    if ($xref) {
    	$url .= '&xref='.$xref;
    }
    $this->setRedirect( $url, $msg, $type );    
	}
	
	function import()
	{
		JRequest::setVar('view', 'importsubmitters');
		parent::display();
	}
	
	function doimport()
	{
		$form_id = JRequest::getInt('form_id');
		$model = $this->getModel('importsubmitters');
		$model->setFormId($form_id);
		
		$base_columns = array( JText::_('COM_REDFORM_Submission_date'),
		                       JText::_('COM_REDFORM_Form_name'),
		                       JText::_('COM_REDFORM_Unique_id') );
		
		$msg = '';
		if ( $file = JRequest::getVar( 'importfile', null, 'files', 'array' ) )
		{
			$handle = fopen($file['tmp_name'],'r');
			if(!$handle) 
			{
				$msg = JText::_('COM_REDFORM_CANNOT_OPEN_UPLOADED_FILE');
				$this->setRedirect( 'index.php?option=com_redform&view=submitters&form_id='.$form_id, $msg, 'error' );
				return;
			}
			
			// get fields, on first row of the file
			$fields = array();
			if ( ($data = fgetcsv($handle, 0, ',', '"')) !== FALSE )
			{
				$numfields = count($data);
				for ($c=0; $c < $numfields; $c++)
				{
					if (in_array($data[$c], $base_columns)) {
						$fields[$c] = $data[$c];												
					}
					else if ($res = $model->getFieldByName($data[$c])) {
						$fields[$c] = 'field_'.$res->id;						
					}
				}
			}
			// If there is no validated fields, there is a problem...
			if ( !count($fields) ) {
				$msg = JText::_('COM_REDFORM_SUBMITTERS_IMPORT_ERROR_PARSING_COLUMNS');
				$this->setRedirect( 'index.php?option=com_redform&view=submitters&form_id='.$form_id, $msg, 'error' );
				return;
			}
			else {
				$msg = JText::sprintf('COM_REDFORM_SUBMITTERS_PARSING_COLUMNS_D_FIELDS_D_KEPT', $numfields, $fields);
			}
			// Now get the records, meaning the rest of the rows.
			$records = array();
			$row = 1;
			while ( ($data = fgetcsv($handle, 0, ',', '"')) !== FALSE )
			{
				$num = count($data);
				if ($numfields != $num) {
					$msg .= JText::sprintf('COM_REDFORM_SUBMITTERS_WRONG_NUMBER_OF_FIELDS_D_ON_ROW_D', $num, $row);
				}
				else {
					$r = array();
					// only extract columns with validated header, from previous step.
					foreach ($fields as $k => $v) {
						$r[$v] = $data[$k];
					}
					$records[] = $r;
				}
				$row++;
			}
			fclose($handle);
			$msg .= JText::sprintf('COM_REDFORM_SUBMITTERS_TOTAL_ROWS_FOUND_D', count($records));
							 
			// database update
			if (count($records))
			{
				if ($result = $model->import($records)) {
					$msg .= JText::sprintf('COM_REDFORM_SUBMITTERS_TOTAL_ADDED_D', $result);				
				}
			}
			$this->setRedirect( 'index.php?option=com_redform&view=submitters&form_id='.$form_id, $msg );
		}
		else {
			$msg = JText::_('COM_REDFORM_IMPORT_FILE_NOT_FOUND');
			$this->setRedirect( 'index.php?option=com_redform&view=submitters&form_id='.$form_id, $msg, 'error' );
			return;
		}
	}
}
?>
