<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );?>
<?php
/* Include redFORM */
JPluginHelper::importPlugin( 'content' );
$dispatcher = JDispatcher::getInstance();
$form = new stdClass();
$form->text = '{redform}'.JRequest::getInt('form_id').',1{/redform}';
$results = $dispatcher->trigger('PrepareEvent', array($form));
if (!isset($results[0])) {
	$redform = JText::_('REGISTRATION_NOT_POSSIBLE');
}
else $redform = $results[0];
echo $redform;
echo '<br />';
echo JTEXT::_('JOOMLA_USER');
echo JHTML::_('list.users', 'user_id', '', 1, NULL, 'name', 0 );
echo '</form>';
JHTML::_('behavior.keepalive'); 
?>
