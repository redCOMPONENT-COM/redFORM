/**
 * @copyright Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
// Export var
var redformPrice;

(function($){

	redformPrice = function(formbox) {

		var form = formbox.parents('form').first();

		var price = 0.0;
		var vat = 0.0;

		function updatePrice() {
			var totalElement = formbox.find(".totalprice");

			if (!totalElement)
			{
				return;
			}

			var price = getPrice();
			var roundedPrice = accounting.formatMoney(price.price + price.vat, {symbol: price.currency, precision: price.precision, thousand: price.thSeparator, decimal: price.decSeparator, format: '%s %v'});

			text = ' <span>' + roundedPrice + '</span>';

			formbox.find(".totalprice").html(text);
		}

		function getPrice() {
			price = 0.0;
			vat = 0.0;

			formbox.find("input.rfprice").each(function() {
				addPrice($(this).val(), $(this).attr('vat'));
			});

			formbox.find(":checked, .eventprice, .fixedprice, .bookingprice").each(function() {
				addPrice($(this).attr('price'), $(this).attr('vat'));
			});

			if (round_negative_price) {
				price = Math.max(price, 0);
				vat = Math.max(vat, 0);
			}

			var currencyField = form.find('input[name="currency"]');
			var currency = (currencyField && currencyField.val()) ? currencyField.val() : '';
			var precision = currencyField ? $(currencyField).attr('precision') : 2;
			var decSeparator = currencyField ? $(currencyField).attr('decimal') : '.';
			var thSeparator = currencyField ? $(currencyField).attr('thousands') : ' ';

			return {
				'price': price,
				'vat': vat,
				'currency': currency,
				'precision': precision,
				'decSeparator': decSeparator,
				'thSeparator': thSeparator
			}
		}

		function addPrice(elementPrice, elementValue)
		{
			var floatPrice = 0;
			var floatVat = 0;

			if (elementPrice)
			{
				floatPrice = parseFloat(elementPrice);
			}

			if (elementValue)
			{
				floatVat = parseFloat(elementValue);
			}

			price += floatPrice;

			if (floatVat)
			{
				vat += floatPrice * floatVat / 100;
			}
		}

		return {
			init : function() {
				formbox.find(':input').change(function() {
					updatePrice();
				})
			},
			updatePrice : updatePrice,
			getPrice: getPrice
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
