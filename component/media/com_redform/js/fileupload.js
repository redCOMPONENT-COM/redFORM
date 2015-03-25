/**
 * @copyright Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
(function ($) {

	// dom ready
	$(function() {
		$('.remove-upload').click(function(){
			$(this).parent().find('input').val('');
			$(this).parent().find('span').text('');
			$(this).remove();
		});
	});

})(jQuery)
