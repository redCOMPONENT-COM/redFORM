<?php
/**
 * @version 1.0 $Id: default.php 288 2009-06-22 15:42:41Z julien $
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

defined('_JEXEC') or die('Restricted access');
?>
<?php foreach ($this->log as $line): ?>
<?php echo $line . '<br/>'; ?>
<?php endforeach; ?>

<form action="index.php" method="post" name="adminForm">  
  <input type="hidden" name="option" value="com_redform" />
  <input type="hidden" name="view" value="log" />
  <input type="hidden" name="task" value="" />
</form>