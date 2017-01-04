/**
 * @copyright  Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Unobtrusive Form Validation library
 *
 * Inspired by: the original joomla 3.4.x validation.js, inspired by Chris Campbell <www.particletree.com> a
 *
 * @since  3.0
 */
var RedFormValidator = function() {
	"use strict";

	var inputEmail;
	var handlers = {};
	var custom = {};

 	var setHandler = function(name, fn, en) {
 	 	en = (en === '') ? true : en;
 	 	handlers[name] = {
 	 	 	enabled : en,
 	 	 	exec : fn
 	 	};
 	};

 	var findLabel = function(id, form){
 	 	var $label, $form = jQuery(form);
 	 	if (!id) {
 	 	 	return false;
 	 	}
 	 	$label = $form.find('#' + id + '-lbl');
 	 	if ($label.length) {
 	 	 	return $label;
 	 	}
 	 	$label = $form.find('label[for="' + id + '"]');
 	 	if ($label.length) {
 	 	 	return $label;
 	 	}
 	 	return false;
 	};

 	var handleResponse = function(state, $el) {
 		// Get a label
 	 	var $label = $el.data('label');
 	 	if ($label === undefined) {
 	 		$label = findLabel($el.attr('id'), $el.get(0).form);
 	 		$el.data('label', $label);
 	 	}

 	 	// Set the element and its label (if exists) invalid state
 	 	if (state === false) {
 	 	 	$el.addClass('invalid').attr('aria-invalid', 'true');
 	 	 	if ($label) {
 	 	 	 	$label.addClass('invalid').attr('aria-invalid', 'true');
 	 	 	}
 	 	} else {
 	 	 	$el.removeClass('invalid').attr('aria-invalid', 'false');
 	 	 	if ($label) {
 	 	 	 	$label.removeClass('invalid').attr('aria-invalid', 'false');
 	 	 	}
 	 	}
 	};

 	var validate = function(el) {
		setElementError(el, '');
 	 	var $el = jQuery(el), tagName, handler;
 	 	// Ignore the element if its currently disabled, because are not submitted for the http-request. For those case return always true.
 	 	if ($el.attr('disabled')) {
 	 	 	handleResponse(true, $el);
 	 	 	return true;
 	 	}
 	 	// If the field is required make sure it has a value
 	 	if ($el.attr('required') || $el.hasClass('required')) {
 	 	 	tagName = $el.prop("tagName").toLowerCase();
 	 	 	if (tagName === 'fieldset' && ($el.hasClass('radio') || $el.hasClass('checkboxes'))) {
 	 	 	 	if (!$el.find('input:checked').length){
 	 	 	 	 	handleResponse(false, $el);
 	 	 	 	 	return false;
 	 	 	 	}
 	 	 	//If element has class placeholder that means it is empty.
 	 	 	} else if (!$el.val().trim() || $el.hasClass('placeholder') || ($el.attr('type') === 'checkbox' && !$el.is(':checked'))) {
				setElementError(el, Joomla.JText._('LIB_REDFORM_FORM_VALIDATION_ERROR_FIELD_IS_REQUIRED'));
 	 	 	 	handleResponse(false, $el);
 	 	 	 	return false;
 	 	 	}
 	 	}
 	 	// Only validate the field if the validate class is set
 	 	handler = ($el.attr('class') && $el.attr('class').match(/validate-([a-zA-Z0-9\_\-]+)/)) ? $el.attr('class').match(/validate-([a-zA-Z0-9\_\-]+)/)[1] : "";
 	 	if (handler === '') {
 	 	 	handleResponse(true, $el);
 	 	 	return true;
 	 	}
 	 	// Check the additional validation types
 	 	if ((handler) && (handler !== 'none') && (handlers[handler]) && $el.val()) {
 	 	 	// Execute the validation handler and return result
 	 	 	if (handlers[handler].exec($el.val(), $el) !== true) {
 	 	 	 	handleResponse(false, $el);
 	 	 	 	return false;
 	 	 	}
 	 	}
 	 	// Return validation state
 	 	handleResponse(true, $el);
 	 	return true;
 	};

 	var isValid = function(form) {
 		var fields, valid = true, message, error, label, invalid = [], i, l;
 	 	// Validate form fields
 	 	fields = jQuery(form).find('input, textarea, select, fieldset');
 	 	for (i = 0, l = fields.length; i < l; i++) {
 	 	 	if (validate(fields[i]) === false) {
 	 	 	 	valid = false;
 	 	 	 	invalid.push(fields[i]);
 	 	 	}
 	 	}
 	 	// Run custom form validators if present
 	 	jQuery.each(custom, function(key, validator) {
 	 	 	if (validator.exec() !== true) {
 	 	 	 	valid = false;
 	 	 	}
 	 	});
 	 	if (!valid && invalid.length > 0) {
 	 	 	message = Joomla.JText._('LIB_REDFORM_VALIDATION_FIELD_INVALID');
 	 	 	error = {"error": []};
 	 	 	for (i = invalid.length - 1; i >= 0; i--) {
 	 	 		label = jQuery(invalid[i]).data("label");
 	 			if (label) {
					var fieldmessage = message + label.text().replace("*", "");

					if (invalid[i].validationMessage) {
						fieldmessage += " - " + invalid[i].validationMessage;
					}

 	 	 			error.error.push(fieldmessage);
				}
 	 	 	}
 	 	 	Joomla.renderMessages(error);
 	 	}
 	 	return valid;
 	};

 	var attachToForm = function(form) {
 	 	var inputFields = [], elements,
 	 		$form = jQuery(form);
 	 	// Iterate through the form object and attach the validate method to all input fields.
 	 	elements = $form.find('input, textarea, select, fieldset, button');
 	 	for (var i = 0, l = elements.length; i < l; i++) {
 	 	 	var $el = jQuery(elements[i]), tagName = $el.prop("tagName").toLowerCase();
 	 	 	// Attach isValid method to submit button
 	 	 	if ((tagName === 'input' || tagName === 'button') && ($el.attr('type') === 'submit' || $el.attr('type') === 'image')) {
 	 	 	 	if ($el.hasClass('validate')) {
 	 	 	 	 	$el.on('click', function() {
 	 	 	 	 	 	return isValid(form);
 	 	 	 	 	});
 	 	 	 	}
 	 	 	}
 	 	 	// Attach validate method only to fields
 	 	 	else if (tagName !== 'button' && !(tagName === 'input' && $el.attr('type') === 'button')) {
 	 	 	 	if ($el.hasClass('required')) {
 	 	 	 	 	$el.attr('aria-required', 'true').attr('required', 'required');
 	 	 	 	}
 	 	 	 	if (tagName !== 'fieldset') {
 	 	 	 	 	$el.on('blur', function() {
 	 	 	 	 	 	return validate(this);
 	 	 	 	 	});
 	 	 	 	 	if ($el.hasClass('validate-email') && inputEmail) {
 	 	 	 	 	 	$el.get(0).type = 'email';
 	 	 	 	 	}
 	 	 	 	}

 	 	 	 	inputFields.push($el);
 	 	 	}
 	 	}

		if ($form.find('.checkboxes.required')) {

			$form.find('.checkboxes.required input').change(function(){
				var fieldset = jQuery(this).parents('fieldset').first();
				fieldset.removeClass('invalid');
				jQuery(fieldset).find('input').get().each(function(input){
					input.setCustomValidity('');
				});
			});

			$form.submit(function(){
				var valid = true;

				$form.find('.checkboxes.required').get().each(function(fieldset){
					var $fieldset = jQuery(fieldset);
					if ($fieldset.find(':checked').length === 0) {
						$fieldset.addClass('invalid');
						var boxes = $fieldset.find('input').get();

						if (boxes.length == 1) {
							boxes[0].setCustomValidity(Joomla.JText._('COM_REDFORM_VALIDATION_CHECKBOX_IS_REQUIRED'));
						}
						else {
							boxes[0].setCustomValidity(Joomla.JText._('COM_REDFORM_VALIDATION_CHECKBOXES_ONE_IS_REQUIRED'));
						}

						if (typeof boxes[0].reportValidity === 'function') {
							boxes[0].reportValidity();
						}

						valid = false;
					}
				});

				return valid;
			});
		}

 	 	$form.data('inputfields', inputFields);
 	};

 	var setElementError = function (el, msg) {
		if (typeof el.setCustomValidity === "function") {
			el.setCustomValidity(msg);

			if (msg && typeof el.reportValidity === "function") {
				el.reportValidity();
			}
		}
	};

	// Initialize handlers and attach validation to form
 	jQuery(function() {
 	 	inputEmail = (function() {
 	 	 	var input = document.createElement("input");
 	 	 	input.setAttribute("type", "email");
 	 	 	return input.type !== "text";
 	 	})();

 	 	// Default handlers
 	 	setHandler('username', function(value, element) {
 	 	 	var regex = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&]", "i");
			var result = !regex.test(value);
			if (!result) {
				setElementError(element.get(0), Joomla.JText._('LIB_REDFORM_VALIDATION_ERROR_USERNAME_INVALID_FORMAT'));
			}
			else {
				setElementError(element.get(0), '');
			}
			return result;
 	 	});

 	 	setHandler('password', function(value, element) {
 	 	 	var regex = /^\S[\S ]{2,98}\S$/;
			var result = regex.test(value);
			if (!result) {
				setElementError(element.get(0), Joomla.JText._('LIB_REDFORM_VALIDATION_ERROR_PASSWORD_INVALID_FORMAT'));
			}
			else {
				setElementError(element.get(0), '');
			}
			return result;
 	 	});

 	 	setHandler('numeric', function(value, element) {
 	 		var regex = /^(\d|-)?(\d|,)*\.?\d*$/;
			var result = regex.test(value);
			if (!result) {
				setElementError(element.get(0), Joomla.JText._('LIB_REDFORM_VALIDATION_ERROR_NUMERIC_INVALID_FORMAT'));
			}
			else {
				setElementError(element.get(0), '');
			}
			return result;
 	 	});

 	 	setHandler('email', function(value, element) {
			value = punycode.toASCII(value);
 	 	 	var regex = /^[a-zA-Z0-9.!#$%&’*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
			var result = regex.test(value);
			if (!result) {
				setElementError(element.get(0), Joomla.JText._('LIB_REDFORM_VALIDATION_ERROR_EMAIL_INVALID_FORMAT'));
			}
			else {
				setElementError(element.get(0), '');
			}
			return result;
 	 	});

		setHandler('futuredate', function(value, element) {
			if (!value) {
				return true;
			}

			var today = new Date(new Date().toDateString());
			var val = new Date(value);

			var result = val >= today;

			if (!result) {
				setElementError(element.get(0), Joomla.JText._('LIB_REDFORM_VALIDATION_ERROR_FUTURE_DATE'));
			}
			else {
				setElementError(element.get(0), '');
			}

			return result;
		});

 	 	// Attach to forms with class 'form-validate'
 	 	var forms = jQuery('form.redform-validate');
 	 	for (var i = 0, l = forms.length; i < l; i++) {
 	 	 	attachToForm(forms[i]);
 	 	}
 	});

 	return {
 	 	isValid : isValid,
 	 	validate : validate,
 	 	setHandler : setHandler,
 	 	attachToForm : attachToForm,
 	 	custom: custom
 	};
};

document.redformvalidator = null;
jQuery(function() {
	document.redformvalidator = RedFormValidator();
});
