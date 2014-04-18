/**
 * @copyright Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

window.addEvent('domready', function(){
	/**
	 * dynamic form price calculation
	 */
	document.getElements("div.redform-form").each(redformPrice.bindElements, redformPrice);
});

var redformPrice = {

	bindElements : function(el) {
		el.getElements('input, select').addEvent('change', function() {
			this.updatePrice(el);
		}.bind(this));
		this.updatePrice(el);
	},

	updatePrice : function(el) {
		// get the instance of redform corresponding to the field that triggered the 'change'

		if (el.get('tag') != 'form') {
			var form = el.getParent('form');
		}
		else {
			var form = el;
		}

		var price = 0.0;
		form.getElements("input.rfprice").each(function(element) {
			var p = document.id(element).get('value');
			if (p) {
				price += parseFloat(p);
			}
		});

		form.getElements(":checked").each(function(element) { // works for select list too
			var p = document.id(element).get('price');
			if (p) {
				price += parseFloat(p);
			}
		});

		form.getElements(".eventprice").each(function(element) {
			var p = document.id(element).get('price');
			if (p) {
				price += parseFloat(p);
			}
		});

		form.getElements(".fixedprice").each(function(element) {
			var p = document.id(element).get('price');
			if (p) {
				price += parseFloat(p);
			}
		});

		form.getElements(".bookingprice").each(function(element) {
			var p = document.id(element).get('price');
			if (p) {
				price += parseFloat(p);
			}
		});

		if (round_negative_price) {
			price = Math.max(price, 0);
		}

		if (form.getElement("#totalprice")) {
			form.getElement("#totalprice").remove();
		}

		// set the price
		if (price) {
			var currency = form.getElement('[name=currency]').get('value');
			var roundedPrice = Math.round(price*100)/100;

			// insert total right after last div.redform-form
			var el = new Element('div', {id : 'totalprice', 'class' : "fieldline"})
				.set('html',
					'<div class="label">' + Joomla.JText._("COM_REDFORM_Total_Price") + '</div>'
						+ '<div class="field">' +currency + ' <span>' + roundedPrice + '</span></div>'
				)
			el.inject(form.getElements('div.formbox').getLast(), 'after');
		}
	}
}
