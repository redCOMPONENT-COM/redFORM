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

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );?>
<?php
/* Include redFORM */
JPluginHelper::importPlugin( 'content' );
$dispatcher = JDispatcher::getInstance();
$form = new stdClass();
$form->text = '{redform}'.$this->form_id.',1{/redform}';
$params = array();
if (isset($this->submitter->uid)) {
	$params['uid'] = $this->submitter->uid;
}
dump($params);
$results = $dispatcher->trigger('onPrepareEvent', array($form, $params));
if (!isset($results[0])) {
	$redform = JText::_('REGISTRATION_NOT_POSSIBLE');
}
else $redform = $results[0];
echo $redform;
JHTML::_('behavior.keepalive'); 
?>
