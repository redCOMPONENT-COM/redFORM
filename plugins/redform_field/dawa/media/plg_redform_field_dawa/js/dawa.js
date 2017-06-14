/**
 * Handles integration with dawa.aws.dk
 */

(function($){

	var escapeRegExChars = function (value) {
		return value.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
	};

	$(function(){
		$('.redform-form .formbox').each(function(index, formbox){
			form = $(formbox);
			form.find('.type-dawa_zip input').autocomplete({
				serviceUrl: "https://dawa.aws.dk/postnumre/autocomplete",
				dataType: 'jsonp',
				paramName: 'q',
				minChars: 1,
				transformResult: function(jsonresp){
					return {
						suggestions: $.map(jsonresp, function(dataItem) {
							return {value: dataItem.postnummer.nr, data: dataItem.postnummer};
						})
					};
				},
				formatResult : function (suggestion, currentValue) {
					var pattern = '(' + escapeRegExChars(currentValue) + ')';

					return suggestion.value.replace(new RegExp(pattern, 'gi'), '<strong>$1<\/strong>') + ' ' + suggestion.data.navn;
				},
				onSelect : function(suggestion) {
					form.find('.type-dawa_city input').val(suggestion.data.navn);
				}
			});

			form.find('.type-dawa_address input').autocomplete({
				serviceUrl: function(query) {
					var url = "https://dawa.aws.dk/vejstykker/autocomplete";

					var zip = form.find('.type-dawa_zip input');

					if (zip && zip.val()) {
						url = url + '?postnr=' + zip.val();
					}

					return url;
				},
				dataType: 'jsonp',
				paramName: 'q',
				minChars: 2,
				transformResult: function(jsonresp){
					return {
						suggestions: $.map(jsonresp, function(dataItem) {
							return {value: dataItem.vejstykke.navn, data: dataItem.vejstykke};
						})
					};
				},
				onSelect : function(suggestion) {
					var streetcodeElement = form.find('.type-dawa_streetcode input');
					if (streetcodeElement) {
						streetcodeElement.val(suggestion.data.kode);
					}

					var municipalityNumberElement = form.find('.type-dawa_municipalitynumber');
					if (municipalityNumberElement) {
						municipalityNumberElement.val(suggestion.data.kommunekode);
					}
				}
			});
		})
	});
})(jQuery);
