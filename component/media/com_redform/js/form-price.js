/**
 * @copyright Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
// Export var
var redformPrice;

(function($){

	redformPrice = function(formbox) {

		var form = formbox.parents('form').first();

		function updatePrice() {
			var price = calcPrice();
			displayPrice(price);
		}

		function calcPrice() {
			var price = 0.0;

			formbox.find("input.rfprice").each(function() {
				var p = $(this).val();
				if (p) {
					price += parseFloat(p);
				}
			});

			formbox.find(":checked").each(function() { // works for select list too
				var p = $(this).attr('price');
				if (p) {
					price += parseFloat(p);
				}
			});

			formbox.find(".eventprice").each(function() {
				var p = $(this).attr('price');
				if (p) {
					price += parseFloat(p);
				}
			});

			formbox.find(".fixedprice").each(function() {
				var p = $(this).attr('price');
				if (p) {
					price += parseFloat(p);
				}
			});

			formbox.find(".bookingprice").each(function(element) {
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
			var totalElement = formbox.find(".totalprice");
			var decSeparator = $(totalElement).attr('decimal');
			var thSeparator = $(totalElement).attr('thousands');

			if (!totalElement)
			{
				return;
			}

			// set the price
			var text = '';

			var currencyField = form.find('input[name="currency"]');
			var currency = (currencyField && currencyField.val()) ? currencyField.val() : '';
			var precision = currencyField ? $(currencyField).prop('precision') : 2;

			var roundedPrice = accounting.formatMoney(price, {symbol: currency, precision: precision, thousand: thSeparator, decimal: decSeparator, format: '%s %v'});

			text += ' <span>' + roundedPrice + '</span>';

			formbox.find(".totalprice").html(text);
		}

		return {
			init : function() {
				formbox.find(':input').change(function() {
					updatePrice();
				})
			},

			updatePrice : updatePrice
		}
	};

	$(function(){
		$("div.redform-form .formbox").each(function() {
			var updater = redformPrice($(this));
			updater.init();
			updater.updatePrice();
		});
	});
})(jQuery);
