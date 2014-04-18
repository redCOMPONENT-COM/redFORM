/**
 * @copyright Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
(function ($) {
	$(function() {
		var redformvalues = new function() {

			var instance = this;
			var fieldId = $('#jform_id').val();

			this.init = function() {
				$('#valuesTable').on('click', '.save-option', this.saveOption);
				$('#valuesTable').on('click', '.remove-option', this.removeOption);
				$('#valuesTable').on('change', '[name^=option-]', this.highlightSave);

				this.getValues();
			}

			this.getValues = function() {
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
							instance.addOption(data[i]);
						}
					}
				});
			}

			this.addOption = function(data) {
				// Reset new option row
				$('#newvalue').find(':input').val(null);

				var tr = this.createRow(data);
				$('#newvalue').before(tr);
			}

			this.updateOption = function(element, data) {
				var tr = this.createRow(data);
				element.replaceWith(tr);
			}

			this.createRow = function(data) {
				var tr = $('#newvalue').clone().removeAttr('id');
				tr.find('span.hide').removeClass('hide');
				tr.find('[name^=option-id]').val(data.id);
				tr.find('[name^=option-value]').val(data.value);
				tr.find('[name^=option-label]').val(data.label);
				tr.find('[name^=option-price]').val(data.price);
				tr.find('[name^=order]').attr('name', 'order[]').val(data.ordering);
				tr.find('td.buttons .save-option').text(Joomla.JText._("COM_REDFORM_JS_FIELD_VALUES_SAVE")).removeClass('btn-success').addClass('btn-primary');

				var btnremove = $('<button/>', {
					'type' : 'button',
					'class': 'remove-option btn btn-danger btn-sm',
					'optionId': data.id
				}).text(Joomla.JText._("COM_REDFORM_JS_FIELD_VALUES_DELETE"));

				tr.find('td.buttons .save-option').after(btnremove);

				return tr;
			}

			this.saveOption = function(event) {
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
							instance.addOption(data);
						}
						else {
							instance.updateOption(parent, data);
						}
					}
				});
			}

			this.removeOption = function(event) {
				var element = $(event.currentTarget);
				$.ajax({
					url: 'index.php?option=com_redform&task=field.removeValue&format=json&view=field&id=' + fieldId,
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

			this.highlightSave = function(event) {
				$('.save-option', $(this).closest('tr')).removeClass('btn-primary').addClass('btn-warning');
			}

			this.init();
		};
	});
})(jQuery);
