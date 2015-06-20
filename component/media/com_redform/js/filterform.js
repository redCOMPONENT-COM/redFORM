/**
 * @copyright Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
var redformFieldsFilter;

(function ($) {

	redformFieldsFilter = function(fieldName, formElement) {
		var formIdElement = $('[name*="[' + formElement + ']"]');
		var formFieldsElement = $('[name="' + fieldName + '"]');

		if (!formIdElement) {
			return;
		}

		formIdElement.change(function(){
			var formId = $(this).val();
			$.ajax({
				url: 'index.php?option=com_redform&format=json&task=fields.filter&formId=' + formId,
				dataType: 'json'
			}).done(function(data) {
				var selected = formFieldsElement.find('option:selected').map(function(i, element) {
					return element.value;
				}).get();

				formFieldsElement.empty();

				data.forEach(function(element) {
					var option = $('<option/>').val(element.value).text(element.text);
					if ($.isArray(selected) && selected.indexOf(element.value) > -1) {
						option.prop('selected', 'selected');
					}
					option.appendTo(formFieldsElement);
				});

				formFieldsElement.trigger("chosen:updated").trigger("liszt:updated");
			});

		}).trigger('change');
	};

})(jQuery)
