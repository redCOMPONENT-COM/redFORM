jQuery(document).ready(function($) {
	var elements = {},
		checkVisible = function(element) {
			var target = element.attr('rel').replace('rfshowon_', ''), targetEl = $('[name=\"' + target+'\"]');
			if (!targetEl.length)
			{
				// for "multiple" select and checkboxes
				targetEl = $('[name=\"' + target+'\[]"]');
			}

			var visible = false;

			targetEl.each(function(i, conditionElement){
				var conditionElementType = $(conditionElement).attr('type');

				if (conditionElementType == 'checkbox' || conditionElementType == 'radio') {
					visible |= $(conditionElement).is(':checked') && element.hasClass('rfshowon_' + $(conditionElement).val());
				}

				if ($(conditionElement).prop('tagName') == 'SELECT') {
					visible |= element.hasClass('rfshowon_' + $(conditionElement).val());
				}
			});

			if (visible) {
				element.slideDown();
			}
			else {
				element.slideUp();
			}
		};
	$('[rel^=\"rfshowon_\"]').each(function(){
		var el = $(this), target = el.attr('rel').replace('rfshowon_', ''), targetEl = $('[name=\"' + target+'\"]');
		if (!elements[target]) {
			if (!targetEl.length)
			{
				// for "multiple" select and checkboxes
				targetEl = $('[name=\"' + target+'\[]"]');
			}

			var targetType = targetEl.attr('type');
			targetEl.bind('change', function(){
				checkVisible(el);
			}).bind('click', function(){
				checkVisible(el);
			}).each(function(){
				checkVisible(el);
			});
			elements[target] = true;
		}
	});
});
