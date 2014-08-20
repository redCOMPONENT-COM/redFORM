/**
 * @copyright Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
// Export var
var redformPrice;

(function($){

	redformPrice = function(formbox) {

		function updatePrice() {
			var price = calcPrice();
			displayPrice(price);
		}

		function calcPrice () {
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
			var line = formbox.find('.priceline');

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

			var currencyField = formbox.parents('form').find('[name=currency]');

			if (currencyField && currencyField.val()) {
				text = currencyField.val();
			}

			var roundedPrice = Math.round(price*100)/100;
			text += ' <span>' + roundedPrice + '</span>';

			formbox.find(".totalprice").html(text);
			line.show();
		}

		formbox.find(':input').change(function() {
			updatePrice();
		})

		updatePrice();
	};

	$(function(){
		$("div.redform-form .formbox").each(function() {
			redformPrice($(this));
		});
	});
})(jQuery);
