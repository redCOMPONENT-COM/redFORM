/**
 * This scripts handle the progressbar of sections
 *
 * @copyright Copyright (C) 2016 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
var setRedFormProgress = (function($){

	var switchSection = function(formbox, current) {
		var active = current + 1;
		$(formbox).find('.form-progress .circle').removeClass().addClass('circle');
		$(formbox).find('.form-progress .bar').removeClass().addClass('bar');
		$(formbox).find('.form-progress .circle:nth-of-type(' + active + ')').addClass('active');
		$(formbox).find('.form-progress .circle:nth-of-type(' + (active - 1) + ')').removeClass('active').addClass('done');
		$(formbox).find('.form-progress .circle:nth-of-type(' + (active - 1) + ') .label').html('&#10003;');
		$(formbox).find('.form-progress .circle:nth-of-type(' + (active - 1) + ')').addClass('active');
		$(formbox).find('.form-progress .circle:nth-of-type(' + (active - 2) + ')').removeClass('active').addClass('done');
	}

	return switchSection;
})(jQuery);
