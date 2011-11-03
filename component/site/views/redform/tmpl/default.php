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

defined('_JEXEC') or die('Restricted access');

	if ($this->vmsettings->virtuemartactive) {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect(JRoute::_('index.php?page=shop.product_details&product_id='.$this->vmsettings->vmproductid.'&option=com_virtuemart&Itemid='.$this->vmsettings->vmitemid));
	}
	else {
		if ($this->productdetails) {
			if (!stristr('http', $this->productdetails->product_full_image)){ 
				$productimage = JURI::root().'/components/com_virtuemart/shop_image/product/'.$this->productdetails->product_full_image;
			}
			else $productimage = $this->productdetails->product_full_image;
			echo '<div id="productimage">'.JHTML::_('image', $productimage, $this->productdetails->product_name).'</div>';
			echo '<div id="productname">'.$this->productdetails->product_name.'</div>';
		}
	}
?>
