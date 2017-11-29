(function($) {
    var isInvalidDate = function($field) {
        var excluded = $field.data('excluded');

        if (!excluded) {
            return function (date) {
                        return false;
            }
        }

        var excludedDates = $field.data('excluded').split(",");

        return function (date) {
            return excludedDates.indexOf(date.format('YYYY-MM-DD')) !== -1;
        }
    };
    
    $(function(){
        $('input.rfdaterange').each(function() {
            $this = $(this);

            var dateFormat = $this.data('format');

            $this.daterangepicker({
                isInvalidDate: isInvalidDate($this),
                autoUpdateInput: false,
                locale: {
                    cancelLabel: Joomla.JText._("PLG_REDFORM_FIELD_DATERANGE_JS_CLEAR")
                }
            });

            $this.on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format(dateFormat) + ' - ' + picker.endDate.format(dateFormat));
            });

            $this.on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        });
    });
})(jQuery);