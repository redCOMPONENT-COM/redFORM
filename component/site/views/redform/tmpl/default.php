<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */

defined('_JEXEC') or die('Restricted access');

	if ($this->vmsettings->virtuemartactive) {
		global $mainframe;
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
