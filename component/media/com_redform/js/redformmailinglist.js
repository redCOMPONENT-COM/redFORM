/**
 * @copyright Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
(function ($) {
	$(function() {
		$('.rfmailinglistnames').on('click', '.add-ml', function() {
			var current = $(this).closest('div');
			var newline = current.clone();
			newline.children(':input').val('');
			newline.children('.remove-ml').removeClass('hide');
			current.after(newline);
		});
		$('.rfmailinglistnames').on('click', '.remove-ml', function() {
			$(this).closest('div').remove();
		});
	});
})(jQuery);
