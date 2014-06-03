/**
 * @copyright Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
(function($){

	$(document).ready(function () {

		var formid = $('input[name="id"]').val();

		if (formid) {
			$.getJSON('index.php?option=com_redform&task=form.getFields&format=json&id=' + formid)
				.done(function (data) {
					data.each(function() {
						var opt = '<option value="' + $(this).value + '">' + $(this).text + '</option>';
						$('#cr_field').append(opt);
					});
				});
		}
		else {
			$('#cond_recipients').attr('disabled', 'disabled');
		}

		$('#cr_function').change(function(){
			var span = $("#cr_params");
			span.empty();
			if ($(this).val() == 'between') {
				span.append('<input type="text" name="cr_param1" id="cr_param1" class="cr_param" size="10">');
				span.append(' ');
				span.append('<input type="text" name="cr_param2" id="cr_param2" class="cr_param" size="10">');
			}
			if ($(this).val() == 'superior' || $(this).val() == 'inferior') {
				span.append('<input type="text" name="cr_param1" id="cr_param1" class="cr_param" size="10">');
			}
		}).change();

		$('#cr_button').click(function(){
			var line = '';

			// reset error status
			$('#cond_recipients_ui').children('input').removeClass('error');
			$('#cond_recipients_ui').children('select').removeClass('error');

			if (!rfConditionalRecipient.checkEmail($('#cr_email').val())) {
				alert(Joomla.JText._('COM_REDFORM_MISSING_OR_INVALID_EMAIL'));
				$('#cr_email').addClass('error');
				return false;
			}

			if (!$('#cr_name').val()) {
				alert(Joomla.JText._('COM_REDFORM_CONDITIONAL_RECIPIENTS_FROMNAME_REQUIRED'));
				$('#cr_name').addClass('error');
				return false;
			}

			if (!$('#cr_field').val()) {
				alert(Joomla.JText._('COM_REDFORM_CONDITIONAL_RECIPIENTS_FIELD_REQUIRED'));
				$('#cr_field').addClass('error');
				return false;
			}

			line += $("#cr_email").val();
			line += ';' + $("#cr_name").val();
			line += ';' + $("#cr_field").val();
			line += ';' + $("#cr_function").val();
			var check = true;

			$('.cr_param').each(function(){
				if ($(this).val() === '') {
					$(this).addClass('error');
					check = false;
					return false;
				}
				line += ';' + $(this).val();
			});
			if (!check) {
				alert(Joomla.JText._('COM_REDFORM_CONDITIONAL_RECIPIENT_MISSING_PARAMETER'));
				return false;
			}

			$('[id*="cond_recipients"]').val($('[id*="cond_recipients"]').val() + line + "\n");

		});
	});

})(jQuery);

var rfConditionalRecipient = {
		checkEmail : function (value) {
			regex=/^[a-zA-Z0-9._-]+@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,4}$/;
			return regex.test(value);
		}
};
