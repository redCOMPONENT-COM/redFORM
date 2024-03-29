(function($) {
	/**
	 * The elements which control other elements visiblity
	 */
	var conditionElements = {};

	var checkVisible = function(target) {
		var targetEl = $('[name=\"' + target+'\"]');

		if (!targetEl.length)
		{
			// for "multiple" select and checkboxes
			targetEl = $('[name=\"' + target+'\[]"]');
		}

		var conditionElementType = $(targetEl).attr('type');

		$('[rel=\"rfshowon_' + target + '\"]').each(function(i, element){
			var visible = false;
			var $dependantElement = $(element);

			targetEl.each(function(i, conditionElement){
				if (conditionElementType == 'checkbox' || conditionElementType == 'radio') {
					visible |= $(conditionElement).is(':checked') && $dependantElement.hasClass('rfshowon_' + $(conditionElement).val());
				}

				if ($(conditionElement).prop('tagName') == 'SELECT') {
					var value = $(conditionElement).val();

					if (value == null) {
						return;
					}

					if (!$.isArray(value)) {
						value = [value];
					}

					value.forEach(function (val) {
						visible |= $dependantElement.hasClass('rfshowon_' + val);
					});
				}
			});

			if (visible) {
				$dependantElement.slideDown();
				$dependantElement.find('.showon-required').attr('required', 'required');
			}
			else {
				$dependantElement.slideUp();
                $dependantElement.find('.required, [required]').removeAttr('required').removeClass('required')
					.each(function(index, el) {
                        if (typeof el.setCustomValidity === "function") {
                            el.setCustomValidity('');
                        }
					}
				);
			}
		});
	};

	$(function() {
		$('[rel^=\"rfshowon_\"]').each(function(){
			var el = $(this);
			// Save inputs required attribute
            el.find('.required, [required]').addClass('showon-required');

			var target = el.attr('rel').replace('rfshowon_', '');
			var targetEl = $('[name=\"' + target+'\"]');

			if (!conditionElements[target]) {

				if (!targetEl.length)
				{
					// for "multiple" select and checkboxes
					targetEl = $('[name=\"' + target+'\[]"]');
				}

				targetEl.change(function(){
					checkVisible(target);
				}).click(function(){
					checkVisible(target);
				});

				// Initial check
				checkVisible(target);

				conditionElements[target] = true;
			}
		});
	});
})(jQuery);
