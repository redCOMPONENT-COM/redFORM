/**
 * javascript for adding tag to editor
 */
var redformEditorInsertTag = (function($) {
	$(function(){
		$('.tagInsertModal').on('show', function(){
			var $element = $(this);
			var fieldId = $element.attr('field');
			var formId = $element.attr('form');
			$element.find('iframe').attr("src", 'index.php?option=com_redform&view=tags&tmpl=component&field=' + fieldId + '&form_id=' + formId);
		});
	});

	// Provide an interface
	return function(tag, editor) {
		jInsertEditorText(tag, editor);
		$('.tagInsertModal').modal('hide');
	};
})(jQuery);
