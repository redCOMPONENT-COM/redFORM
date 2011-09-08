/**
 * @copyright Copyright (C) 2008, 2009, 2010, 2011 redCOMPONENT.com. All rights reserved. 
* @license	GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
* 
*/

/**
 * dynamic form price calculation
 */
jQuery(function () {
   jQuery("div.redform-form").find(":input").change(updatePrice).slice(0,1).trigger("change");
});

function updatePrice()
{
	// get the instance of redform corresponding to the field that triggered the 'change'
	var instance = jQuery(this).parents("div.redform-form").slice(0,1);
	
	var price = 0.0;
	var active = parseInt(instance.find("input[name='curform']").slice(0,1).val());
	
	var countforms = 0;
	
	for (var i = 1; i < active+1; i++)
	{
		var signup = instance.find("#formfield"+i).slice(0,1);
		signup.find("input.rfprice").each(function(k) {
			var p = jQuery(this).val();
			if (p) {
				price += parseFloat(p);
			}
		});
		
		signup.find("[selected]").each(function() {
			var p = jQuery(this).attr('price');
			if (p) {
				price += parseFloat(p);
			}
		});

		signup.find("[checked]").each(function() {
			var p = jQuery(this).attr('price');
			if (p) {
				price += parseFloat(p);
			}
		});

		signup.find(".eventprice").each(function() {
			var p = jQuery(this).attr('price');
			if (p) {
				price += parseFloat(p);
			}
		});

		signup.find(".fixedprice").each(function() {
			var p = jQuery(this).attr('price');
			if (p) {
				price += parseFloat(p);
			}
		});

		signup.find(".bookingprice").each(function() {
			var p = jQuery(this).attr('price');
			if (p) {
				price += parseFloat(p);
			}
		});
	}
	if (round_negative_price) {
		price = Math.max(price, 0);
	}
	// set the price
	if (price && !instance.find("#totalprice").length) {
		instance.append('<div id="totalprice" class="fieldline"><div class="label">'+totalpricestr+'</div><div class="field">'+currency+' <span></span></div></div>');
	}
	instance.find("#totalprice span").slice(0,1).text(Math.round(price*100)/100);
}