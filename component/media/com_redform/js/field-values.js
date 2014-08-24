/**
 * @copyright Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
(function ($) {
	var redformvalues = (function() {

		var fieldId;

		function init() {
			fieldId = $('#jform_id').val();
			$('#valuesTable').on('click', '.save-option', saveOption);
			$('#valuesTable').on('click', '.remove-option', removeOption);
			$('#valuesTable').on('change', '[name^=option-]', highlightSave);

			getValues();
		}

		function getValues() {
			// Perform the ajax request
			$.ajax({
				url: 'index.php?option=com_redform&task=field.getValues&format=json&view=field&id=' + fieldId,
				dataType: 'json',
				beforeSend: function (xhr) {
					$('.values-content-content .spinner').show();
				}
			}).done(function(data) {
				$('.values-content-content .spinner').hide();

				if (data && data.length) {
					for (var i = 0; i < data.length; i++) {
						addOption(data[i]);
					}
				}
			});
		}

		function addOption(data) {
			// Reset new option row
			$('#newvalue').find(':input').val(null);

			var tr = createRow(data);
			$('#newvalue').before(tr);
		}

		function updateOption(element, data) {
			var tr = createRow(data);
			element.replaceWith(tr);
		}

		function createRow(data) {
			var tr = $('#newvalue').clone().removeAttr('id');
			tr.find('span.hide').removeClass('hide');
			tr.find('[name^=option-id]').val(data.id);
			tr.find('[name^=option-value]').val(data.value);
			tr.find('[name^=option-label]').val(data.label);
			tr.find('[name^=option-price]').val(data.price);
			tr.find('[name^=order]').attr('name', 'order[]').val(data.ordering);
			tr.find('td.buttons .save-option').text(Joomla.JText._("COM_REDFORM_JS_FIELD_VALUES_SAVE")).removeClass('btn-success').removeClass('btn-warning').addClass('btn-primary');

			var btnremove = $('<button/>', {
				'type' : 'button',
				'class': 'remove-option btn btn-danger btn-sm',
				'optionId': data.id
			}).text(Joomla.JText._("COM_REDFORM_JS_FIELD_VALUES_DELETE"));

			tr.find('td.buttons .save-option').after(btnremove);

			return tr;
		}

		function saveOption(event) {
			var parent = $(event.currentTarget).closest('tr');
			var elements = parent.find(':input');
			var id = parent.find('[name^=option-id]').val();

			$.ajax({
				url: 'index.php?option=com_redform&task=field.saveOption&format=json&view=field&id=' + fieldId,
				beforeSend: function (xhr) {
					$('.values-content-content .spinner').show();
				}
				,
				data:elements.serialize(),
				dataType: 'json',
				type : 'POST'
			}).done(function(data) {
				$('.values-content-content .spinner').hide();

				if (data && data.id) {
					if (!id) {
						addOption(data);
					}
					else {
						updateOption(parent, data);
					}
				}
			});
		}

		function removeOption(event) {
			var element = $(event.currentTarget);
			$.ajax({
				url: 'index.php?option=com_redform&task=field.removeOption&format=json&view=field&id=' + fieldId,
				data: {'optionId' : element.attr('optionId')},
				type : 'POST',
				dataType: 'json',
				beforeSend: function (xhr) {
					$('.values-content-content .spinner').show();
				}
			}).done(function(data) {
				$('.values-content-content .spinner').hide();

				if (data && data.success) {
					element.closest('tr').remove();
				}
			});
		}

		function highlightSave(event) {
			$('.save-option', $(this).closest('tr')).removeClass('btn-primary').addClass('btn-warning');
		}

		return {
			init: init
		}
	})();

	$(function() {
		redformvalues.init();
	});
})(jQuery);
