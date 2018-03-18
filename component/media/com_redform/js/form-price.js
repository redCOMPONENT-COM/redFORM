/**
 * @copyright Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
// Export var
var redformPrice;

(function($){

	redformPrice = function(formbox) {

		var form = formbox.parents('form').first();
		var formIndex = formbox.index();
		var formId = form.find('input[name="form_id"]').val();
		var totalElement = formbox.find(".totalprice");

		var externalTotalSelector = '.totalprice' + formId + '_' + (formIndex + 1);

		if (formIndex == 0) {
			externalTotalSelector += ', .totalprice' + formId;
		}

		var externalTotal = $(externalTotalSelector);

		var price = 0.0;
		var vat = 0.0;

		function updatePrice() {
			if (!totalElement && !externalTotal)
			{
				return;
			}

			var price = getPrice();
			var currencyFormat = price.symbolAfter ? '%v %s' : '%s %v';
			var roundedPrice = accounting.formatMoney(price.price + price.vat, {symbol: price.symbol, precision: price.precision, thousand: price.thSeparator, decimal: price.decSeparator, format: currencyFormat});

			text = ' <span>' + roundedPrice + '</span>';

			if (totalElement) {
				totalElement.html(text);
			}

			if (externalTotal) {
                externalTotal.html(text);
			}
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
			var symbol = currencyField ? $(currencyField).attr('symbol') : currency;
			var symbolAfter = currencyField ? $(currencyField).attr('symbolAfter') : 0;
			var precision = currencyField ? $(currencyField).attr('precision') : 2;
			var decSeparator = currencyField ? $(currencyField).attr('decimal') : '.';
			var thSeparator = currencyField ? $(currencyField).attr('thousands') : ' ';

			return {
				'price': price,
				'vat': vat,
				'currency': currency,
				'precision': precision,
				'symbol': symbol,
				'symbolAfter': symbolAfter,
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
