/**
 * Created by julien on 11/12/14.
 */
(function($){

	$(document).ready(function() {
		$('input[type=checkbox].esGas').each(function(){
			gasform(this);
		});
	});

	var escapeRegExChars = function (value) {
		return value.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
	}

	var gasform = function(element) {
		var form = $(element.form);

		var updateRategroups = function() {
			var rates;

			if (form.find('.esGas').attr('checked')) {
				if (form.find('input[value=new].gasel-elec-radio').attr('checked')) {
					rates = '["5899", "5896"]';
				}
				else if (form.find('input[value=already].gasel-elec-radio').attr('checked')) {
					rates = '["5899"]';
				}
				else {
					rates = '["5898"]';
				}
			}
			else if (form.find('input[value=new].gasel-elec-radio').attr('checked')) {
					rates = '["5894"]';
			}

			if (form.find('input[value=new].gasel-elec-radio').attr('checked')) {
				form.find('.type-textfieldnewCustomerMeasurementPointElectricity').show();
			}
			else {
				form.find('.type-textfieldnewCustomerMeasurementPointElectricity').hide();
			}

			form.find('input[name=rategroups]').val(rates);
		};

		$('.gasel-zip').autocomplete({
			serviceUrl: "index.php?option=com_ajax&plugin=gaselkmd&format=json&function=postcode",
			paramName: 'q',
			minChars: 1,
			transformResult: function(jsonresp){
				var response = JSON.parse(jsonresp);

				if (!response.data[0]) {
					return;
				}

				return {
					suggestions: $.map(response.data[0], function(dataItem) {
						return {value: dataItem.nr, data: dataItem};
					})
				};
			},
			formatResult : function (suggestion, currentValue) {
				var pattern = '(' + escapeRegExChars(currentValue) + ')';

				return suggestion.value.replace(new RegExp(pattern, 'gi'), '<strong>$1<\/strong>') + ' ' + suggestion.data.navn;
			},
			onSelect : function(suggestion) {
				form.find('.gasel-city').val(suggestion.data.navn);
			}
		});

		form.find('input.esGas, input.gasel-elec-radio').click(updateRategroups);
		updateRategroups();
	}

})(jQuery);
