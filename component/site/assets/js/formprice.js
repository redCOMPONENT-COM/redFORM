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

window.addEvent('domready', function(){
	/**
	 * dynamic form price calculation
	 */
	document.getElements("div.redform-form select").addEvent('change', updatePrice);
	document.getElements("div.redform-form input").addEvent('change', updatePrice).getLast().fireEvent("change");
});

function updatePrice()
{
	// get the instance of redform corresponding to the field that triggered the 'change'
	var instance = document.id(this.form);
	
	var price = 0.0;
	var active = parseInt(instance.getElement("input[name='curform']").getProperty('value'));
		
	for (var i = 1; i < active+1; i++)
	{
		var signup = instance.getElement("#formfield"+i);
		signup.getElements("input.rfprice").each(function(element) {
			var p = document.id(element).getProperty('value');
			if (p) {
				price += parseFloat(p);
			}
		});
		
		signup.getElements(":checked").each(function(element) { // works for select list too
			var p = document.id(element).getProperty('price');
			if (p) {
				price += parseFloat(p);
			}
		});

		signup.getElements(".eventprice").each(function(element) {
			var p = document.id(element).getProperty('price');
			if (p) {
				price += parseFloat(p);
			}
		});

		signup.getElements(".fixedprice").each(function(element) {
			var p = document.id(element).getProperty('price');
			if (p) {
				price += parseFloat(p);
			}
		});

		signup.getElements(".bookingprice").each(function(element) {
			var p = document.id(element).getProperty('price');
			if (p) {
				price += parseFloat(p);
			}
		});
	}
	if (round_negative_price) {
		price = Math.max(price, 0);
	}
	// set the price
	if (price && !instance.getElement("#totalprice")) {
		// insert total right after last div.redform-form
		new Element('div', {id : 'totalprice', 'class' : "fieldline"})
		    .set('html', '<div class="label">'+totalpricestr+'</div><div class="field">'+currency+' <span></span></div>')
		    .injectAfter(instance.getElements('.redform-form').getLast());
		instance.getElement("#totalprice span").set('text', Math.round(price*100)/100);
	}
	else if (instance.getElement("#totalprice")) {
		instance.getElement("#totalprice span").set('text', Math.round(price*100)/100);
	}
}