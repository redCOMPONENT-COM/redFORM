/**
 * Created by julien on 11/12/14.
 */
(function($){

	$(document).ready(function() {
		$('input[type=checkbox].kmd-gas, input[type=checkbox].kmd-elec').each(function(){
			gaselecform(this);
		});
	});

	var escapeRegExChars = function (value) {
		return value.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
	}

	var gaselecform = function(element) {
		var form = $(element.form);
		var isGas = $(element).hasClass('kmd-gas');

		var updateRategroups = function() {
			var rates;

			if (isGas) {
				if (form.find('.kmd-gas').attr('checked')) {
					if (form.find('input[value=new].kmd-elec-radio').attr('checked')) {
						rates = '["5899", "5896"]';
					}
					else if (form.find('input[value=already].kmd-elec-radio').attr('checked')) {
						rates = '["5899"]';
					}
					else {
						rates = '["5898"]';
					}
				}
				else if (form.find('input[value=new].kmd-elec-radio').attr('checked')) {
						rates = '["5894"]';
				}

				if (form.find('input[value=new].kmd-elec-radio').attr('checked')) {
					form.find('.type-textfieldkmd-measurementpointelectricity').show();
				}
				else {
					form.find('.type-textfieldkmd-measurementpointelectricity').hide();
				}
			}
			else {
				if (form.find('.kmd-elec').attr('checked')) {
					if (form.find('input[value=new].kmd-elec-gas').attr('checked')) {
						rates = '["5899", "5896"]';
					}
					else if (form.find('input[value=already].kmd-gas-radio').attr('checked')) {
						rates = '["5896"]';
					}
					else {
						rates = '["5894"]';
					}
				}
				else if (form.find('input[value=new].kmd-gas-radio').attr('checked')) {
					rates = '["5898"]';
				}

				if (form.find('input[value=new].kmd-gas-radio').attr('checked')) {
					form.find('.type-textfieldkmd-measurementpointgas').show();
				}
				else {
					form.find('.type-textfieldkmd-measurementpointgas').hide();
				}
			}

			form.find('input.kmd-rategroups').val(rates);
		};

		$('.kmd-zip').autocomplete({
			serviceUrl: "https://geo.oiorest.dk/postnumre.json",
			dataType: 'jsonp',
			paramName: 'q',
			minChars: 1,
			transformResult: function(jsonresp){
				return {
					suggestions: $.map(jsonresp, function(dataItem) {
						return {value: dataItem.nr, data: dataItem};
					})
				};
			},
			formatResult : function (suggestion, currentValue) {
				var pattern = '(' + escapeRegExChars(currentValue) + ')';

				return suggestion.value.replace(new RegExp(pattern, 'gi'), '<strong>$1<\/strong>') + ' ' + suggestion.data.navn;
			},
			onSelect : function(suggestion) {
				$('.kmd-city').val(suggestion.data.navn);
			}
		});

		$('.kmd-street').autocomplete({
			serviceUrl: function(query) {
				var url = "https://geo.oiorest.dk/vejnavne.json";

				var zip = $('.kmd-zip');

				if (zip && zip.val()) {
					var range = zip.val().split("-");

					if (range.length > 1) {
						url = url + '?frapostnr=' + range[0] + '&tilpostnr=' + range[1];
					}
					else {
						url = url + '?postnr=' + range[0];
					}
				}

				return url;
			},
			dataType: 'jsonp',
			paramName: 'vejnavn',
			minChars: 2,
			transformResult: function(jsonresp){
				return {
					suggestions: $.map(jsonresp, function(dataItem) {
						return {value: dataItem.navn, data: dataItem};
					})
				};
			},
			onSelect : function(suggestion) {
				var streetcodeElement = $('.kmd-streetcode');
				if (streetcodeElement) {
					streetcodeElement.val(suggestion.data.kode);
				}

				var municipalityNumberElement = $('.kmd-municipalitynumber');
				if (municipalityNumberElement) {
					municipalityNumberElement.val(suggestion.data.kommune.kode);
				}
			}
		});

		$('input.kmd-gas, input.kmd-elec, input.kmd-gas-radio, input.kmd-elec-radio').click(updateRategroups);
		updateRategroups();
	}

})(jQuery);
