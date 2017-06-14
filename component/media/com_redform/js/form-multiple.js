/**
 * @copyright Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

(function($){

	var redformMultiple = (function() {

		function updateLinks(form) {
			var $divAdd = form.find('.add-instance');
			$divAdd.find('.subform-links').remove();

			var links = $('<span class="subform-links"></span>');

			form.find('.formbox').each(function(index, element){
				links.append('<button type="button" class="subform-link btn btn-primary" ref="' + index +'" href="#">' + (index + 1) + '</button>');
			});

			$divAdd.append(links);
		}


		function addInstance(event)
		{
			var element = $(event.target);
			var form = element.parents('form');
			var maxform = parseInt(form.find("input[name='multi']").val());
			var subforms = form.find('.formbox');
			var nbactive = subforms.length;
			var last = subforms.last();

			if (nbactive >= maxform)
			{
				alert(Joomla.JText._("COM_REDFORM_MAX_SIGNUP_REACHED"));
				return;
			}

			var newSubForm = last.clone();
			updateIndex(newSubForm, nbactive + 1, true);
			last.after(newSubForm);

			// Update count of active
			form.find("input[name='nbactive']").val(nbactive + 1);

			// trigger price update
			if (redformPrice)
			{
				var updater = redformPrice($(newSubForm));
				updater.init();
				updater.updatePrice();
			}

			updateLinks(form);
		}

		function removeInstance(event) {
			var removeIndex = $(event.target).attr('index');
			var form = $(event.target).parents('form');

			// Remove this instance
			var parent = $(event.target).parents('.formbox').first();
			parent.remove();

			// Shift all others
			var subforms = form.find('.formbox');

			for (var i = removeIndex - 1; i < subforms.length; i++) {
				updateIndex($(subforms[i]), i + 1, false)
			}

			// Update count of active
			form.find("input[name='nbactive']").val(subforms.length);

			// trigger price update
			if (redformPrice)
			{
				redformPrice(form);
			}

			updateLinks(form);
		}

		function updateIndex(subform, index, resetValue)
		{
			subform.find(':input').each(function(i, element) {
				var el = $(element);
				updateAttributeFieldIndex(el, 'name', index);
				updateAttributeFieldIndex(el, 'id', index);

				var preserveValue = ['hidden', 'radio', 'checkbox'];

				if (resetValue && preserveValue.indexOf(el.attr('type')) == -1) {
					el.val(null);
				}
			});

			subform.find('label').each(function(i, element) {
				var el = $(element);
				updateAttributeFieldIndex(el, 'for', index);
			});

			var legend = subform.find('legend').empty();
			var signupTitle = Joomla.JText._("COM_REDFORM_FIELDSET_SIGNUP_NB").replace(/(%d)/, index);
			var deleteLink = $('<span></span>').addClass('remove-instance btn btn-danger').attr('index', index).text(Joomla.JText._("LIB_REDFORM_REMOVE"))
				.click(removeInstance);

			legend.text(signupTitle + ' - ');
			legend.append(deleteLink);
		}

		function updateAttributeFieldIndex(element, attribute, index)
		{
			var attr = element.attr(attribute);

			if (attr)
			{
				attr = attr.replace(/(field[0-9]+_)([0-9]+)/g, "$1" + index);
				element.attr(attribute, attr);
			}
		}

		return {
			init: function() {
				$('.add-instance .button-add').click(addInstance);

				$('.redform-form').each(function(index, element) {
					updateLinks($(element));
				});

				$('.redform-form').on('click', '.subform-link', function(){
					$(this).parents('form').find('.formbox').get($(this).attr('ref')).scrollIntoView();
				});

				$('.subform-toggle .show-signups').click(function(){
					$(this).parents('form').find('.subform-fields').show();
				});

				$('.subform-toggle .hide-signups').click(function(){
					$(this).parents('form').find('.subform-fields').hide();
				});

				$('.redform-form').on('click', '.subform-legend', function(){
					$(this).parents('.formbox').find('.subform-fields').toggle();
				});
			}
		}
	})();

	// dom ready
	$(function() {
		redformMultiple.init();
	});
})(jQuery);
