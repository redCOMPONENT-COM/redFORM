/**
 * @copyright Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
(function ($) {
	// dom ready
	$(function() {
		$('.redform-form .type-fileupload').each(function (_, element) {
			var fileInput = $(element).find('input[type=file]');
			var isRequired = fileInput.hasClass('required');

			if (isRequired) {
				var prev = $(element).find('.current_file input');

				if (prev.val().length > 0) {
					fileInput.removeClass('required');
				}
			}

			$(element).find('.remove-upload').click(function(){
				$(this).parent().find('input').val('');
				$(this).parent().find('span').text('');
				$(this).remove();

				if (isRequired) {
					fileInput.addClass('required');
				}
			});
		});
	});
})(jQuery);
