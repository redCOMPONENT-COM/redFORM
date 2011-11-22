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

/* No direct access */
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view' );

/**
 * redFORM View
 */
class RedformViewSubmitter extends JView {
	
  function display($tpl = null) 
  {
  	$document = &JFactory::getDocument();
  	JHTML::_('behavior.mootools');
  	
  	$js = <<<EOF
			Joomla.submitform = function(pressbutton, form){
				if (pressbutton) {
					document.redform.task.value=pressbutton;
				}
				if (typeof document.redform.onsubmit == "function") {
					document.redform.onsubmit();
				}
				document.redform.submit();
			}
EOF;

  	$document->addScriptDeclaration($js);
  	
  	$submitter = & $this->get('Data');
  	if ($submitter) 
  	{
	  	JRequest::setVar('answers', array($submitter));
	  	JRequest::setVar('submit_key', $submitter->submit_key);
	  	JRequest::setVar('xref', $submitter->xref);
	  	JRequest::setVar('submitter_id', $submitter->id);
  	}
  	JRequest::setVar('redform_edit', true);
  	
  	$this->assignRef('submitter', $submitter);
  	$this->assignRef('form_id',   JRequest::getVar('form_id'));
  	
  	JToolBarHelper::title(JText::_('COM_REDFORM_EDIT_SUBMITTER' ), 'redform_submitters');
  	JToolBarHelper::save();
  	JToolBarHelper::cancel();
        
  	/* Display the page */
  	parent::display($tpl);
  }
}
?>
