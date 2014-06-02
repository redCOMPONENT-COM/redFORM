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

$rfcore = new RdfCore;
?>
<form action="index.php?option=com_redform&controller=submitters" method="post" name="redform"
      id="adminForm" enctype="multipart/form-data" class="form-validate form-horizontal">
<?php echo $rfcore->getFormFields($this->item->form_id, array($this->item->id), 1); ?>
<input type="hidden" name="task" id="task" value="save" />
<input type="hidden" name="integration" id="integration" value="<?php $this->item->integration; ?>" />
</form>
<?php
