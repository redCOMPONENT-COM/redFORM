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

jimport('joomla.html.pane'); 
JHTML::_('behavior.tooltip');
?>
<form action="index.php" method="post" name="adminForm">
	
	<?php foreach ($this->params->getGroups() as $key => $groups): ?>
		<?php $fielset = (strcmp($key, '_default') == 0) ? JText::_('COM_REDFORM_SETTINGS_FIELDSET_GENERAL') : JText::_( strtoupper($key) ); ?>
  		<fieldset class="adminform"><legend><?php echo $fielset; ?></legend>
    <?php echo $this->params->render('params', $key); ?>
    </fieldset>
  <?php endforeach; ?>
    
	<input type="hidden" name="option" value="com_redform" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="configuration" />
</form>
