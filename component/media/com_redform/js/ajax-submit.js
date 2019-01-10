/**
 * @copyright Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

(function($){
	// Do not allow ajax submit if browser doesn't support formData
	if (!("FormData" in window)) {
		return true;
	}

	// dom ready
	$(function() {
		if ($('.redform-ajaxsubmit').length) {
			$('.redform-ajaxsubmit').parents('form').submit(function(event){
				event.preventDefault();
				var $form = $(this);
				var formData = new FormData($form[0]);

				$.ajax({
					url: 'index.php?option=com_redform&task=redform.save&format=json',
					type: "post",
					dataType: "json",
					data: formData,
					contentType: false,
					processData: false
				})
				.done(function(response){
					if (!response.success) {
						alert(response.message);

						return;
					}

					$form.replaceWith("<div class='redform-ajax-response well'>" + response.data + "</div>");
				})
				.fail(function(){
					alert(Joomla.JText._('LIB_REDFORM_AJAX_SUBMIT_ERROR'));
				});

				return false;
			})
		}
	});
})(jQuery);
