/**
 * Created by julien on 11/12/14.
 */
(function($){

	$(document).ready(function() {
		$('input[type=checkbox].esGas').each(function(){
			gasform(this);
		});
	});

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

			form.find('input[name=rategroups]').val(rates);
		};

		form.find('input.esGas, input.gasel-elec-radio').click(updateRategroups);
	}

})(jQuery);
