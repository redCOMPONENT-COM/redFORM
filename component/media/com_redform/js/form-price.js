/**
 * @copyright Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
(function($){

	var redformPrice = function(element) {

		var form;

		if (element.attr('tag') != 'form') {
			form = element.parents('form').first();
		}
		else {
			form = element;
		}

		function updatePrice() {
			var price = calcPrice();
			displayPrice(price);
		}

		function calcPrice () {
			var price = 0.0;

			form.find("input.rfprice").each(function() {
				var p = $(this).val();
				if (p) {
					price += parseFloat(p);
				}
			});

			form.find(":checked").each(function() { // works for select list too
				var p = $(this).attr('price');
				if (p) {
					price += parseFloat(p);
				}
			});

			form.find(".eventprice").each(function() {
				var p = $(this).attr('price');
				if (p) {
					price += parseFloat(p);
				}
			});

			form.find(".fixedprice").each(function() {
				var p = $(this).attr('price');
				if (p) {
					price += parseFloat(p);
				}
			});

			form.find(".bookingprice").each(function(element) {
				var p = $(this).attr('price');
				if (p) {
					price += parseFloat(p);
				}
			});

			if (round_negative_price) {
				price = Math.max(price, 0);
			}

			return price;
		}

		function displayPrice(price) {
			var totalElement = form.find(".totalprice");
			var line = form.find('.priceline');

			if (!totalElement)
			{
				return;
			}

			line.hide();

			if (!(price > 0))
			{
				return;
			}

			// set the price
			var text = '';

			var currencyField = form.find('[name=currency]');

			if (currencyField && currencyField.val()) {
				text = currencyField.val();
			}

			var roundedPrice = Math.round(price*100)/100;
			text += ' <span>' + roundedPrice + '</span>';

			form.find(".totalprice").html(text);
			line.show();
		}

		form.find(':input').change(function() {
			updatePrice();
		})

		updatePrice();
	};

	$(function(){
		$("div.redform-form").each(function() {
			redformPrice($(this));
		});
	});
})(jQuery);
