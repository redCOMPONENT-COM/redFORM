<?php
/**
 * @package     Redform.Site
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$data = $displayData;

$properties = $data->getInputProperties();

$reference = $data->getReferenceField();
$script = <<<JS
	(function($){
		$(function() {
			var refField = $('[id^=field{$reference->id}_]');
			var thisField = $('[id^=field{$data->id}_]');
			
			var check = function () {
				var refValue = refField.val();
				var result = thisField.val() == refValue ? true : false;

				document.redformvalidator.setElementError(thisField.get(0), result ? '' : Joomla.Text._('LIB_REDFORM_FIELD_REPEATTEXT_VALUES_DONT_MATCH'));

				return result;
			}
			
			document.redformvalidator.setHandler('custom{$data->id}', check);
			
			refField.change(function() {
				var required = null;
				
				if ($(this).val().length) {
					thisField.prop('required', true);
				}
				else {
					thisField.removeProp('required');
				}
				
				check();
			});
		});
	})(jQuery);
JS;
JFactory::getDocument()->addScriptDeclaration($script);

Jtext::script('LIB_REDFORM_FIELD_REPEATTEXT_VALUES_DONT_MATCH');
?>
<input <?php echo $data->propertiesToString($properties); ?>/>
