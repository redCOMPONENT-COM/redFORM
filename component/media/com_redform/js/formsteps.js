/**
 * This scripts handle form steps, dividing it per sections.
 *
 * @copyright Copyright (C) 2016 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
(function($){

	var initSteps = function (formbox) {
		var $sections = $(formbox).find('.redform-section');

		var current = 0;
		var total = $sections.length;

		if (total < 2)
		{
			// No steps required
			return;
		}

		$sections.not(':first').hide();

		//Append the form controls to the button
		formbox.append('<div class="form-controls"><input type="button" value="PREVIOUS" id="previous-section" class="section-button" /><input type="button" value="NEXT" id="next-section" class="section-button" /><div class="clear"></div></div>');

		var $controls = formbox.find('.form-controls');
		var $previousLink = formbox.find('#previous-section');
		var $nextLink = formbox.find('#next-section');

		//Go Back click event
		$previousLink.click(function(e) {
			current--;
			changeSection();
		});

		//Go Forward click event
		$nextLink.click(function(e) {
			if (document.redformvalidator.isValid($sections.get(current)))
			{
				current++;
				changeSection();
			}
		});

		var changeSection = function() {
			$sections.hide();
			$sections.eq(current).show();
			showControls(current);

			if ($.isFunction(window.setRedFormProgress)) {
				setRedFormProgress(formbox, current);
			}
		}

		//Show the controls dependent on the forms position.
		var showControls = function() {

			//If our index is less than or equal to zero, we dont
			//need to display the Previous button.
			if(current <= 0){
				$previousLink.hide();
			} else {
				$previousLink.show();
			}

			//If we're more than or equal to the total sections,
			//display the submit button and remove the Next button
			if(current >= total -1){
				$nextLink.hide();
				formbox.parents('form').find('input[type=submit]').show();
			} else {
				$nextLink.show();
				formbox.parents('form').find('input[type=submit]').hide();
			}
		}

		changeSection();
	}

	$(function(){
		$("div.redform-form").each(function(i, rform) {
			var $subforms = $(rform).find(".formbox");

			if ($subforms.length > 1)
			{
				// Do not use this script when there is more than one form
				return;
			}

			initSteps($subforms.first());
		});
	});
})(jQuery);
