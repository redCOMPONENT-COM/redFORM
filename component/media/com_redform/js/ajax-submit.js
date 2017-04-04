/**
 * @copyright Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

(function($){

	// dom ready
	$(function() {
		if ($('.redform-ajaxsubmit').length) {
			$('.redform-ajaxsubmit').parents('form').submit(function(event){
				event.preventDefault();
				var $form = $(this);
				$.ajax({
					url: 'index.php?option=com_redform&task=redform.save&format=json',
					type: "post",
					dataType: "json",
					data: $form.serialize()
				})
				.done(function(response){
					if (!response.success) {
						alert(Joomla.JText._('LIB_REDFORM_AJAX_SUBMIT_ERROR'));

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
