/**
 * @copyright Copyright (C) 2008, 2009, 2010, 2011 redCOMPONENT.com. All rights reserved.
* @license	GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

/**
 * Reform validation script
 *
 * requires language strings to be defined inline
 */

function CheckSubmit(form)
{
	var msg = '';
	var result = true;
	var checkboxmsg = false;
	var radiomsg = false;

	// only check the form that were activated by the user
	var forms = $$('.formbox');
	var nb_active = parseInt(document.getElement("input[name='curform']").getProperty('value'));

	for (var j = 0 ; j < nb_active ; j++)
	{
		// get the input data of the form
		var formelements = document.id(forms[j]).getElements('input').concat(document.id(forms[j]).getElements('select'), document.id(forms[j]).getElements('textarea'));

		for(var i=0; i < formelements.length; i++)
		{
			var check_element = formelements[i];

			/* Check field type */
			/* Fullname */
			if (check_element.name.indexOf("[fullname]") != -1 && check_element.className.match("required")) {
				var fullresult = CheckFill(check_element);
				if (!fullresult) {
					msg += getLabel(check_element).get('text')+': '+"<?php echo JText::_('COM_REDFORM_please_enter_a_name'); ?>\n";
				}
				if (result) result = fullresult;
			}

			/* Text field */
			if (check_element.name.indexOf("[text]") != -1 && check_element.className.match("required")) {
				var textresult = CheckFill(check_element);
				if (!textresult) {
						msg += getLabel(check_element).get('text')+': '+"<?php echo JText::_('COM_REDFORM_JS_CHECK_FIELD_REQUIRED'); ?>\n";
				}
				if (result) result = textresult;
			}

			/* Textarea field */
			if (check_element.name.indexOf("[textarea]") != -1 && check_element.className.match("required")) {
				var textarearesult = CheckFill(check_element);
				if (!textarearesult) {
						msg += getLabel(check_element).get('text')+': '+"<?php echo JText::_('COM_REDFORM_JS_CHECK_FIELD_REQUIRED'); ?>\n";
				}
				if (result) result = textarearesult;
			}

			/* Username field */
			if (check_element.name.indexOf("[username]") != -1 && check_element.className.match("required")) {
				var usernameresult = CheckFill(check_element);
				if (!usernameresult) {
						msg += getLabel(check_element).get('text')+': '+"<?php echo JText::_('COM_REDFORM_please_enter_an_username'); ?>\n";
				}
				if (result) result = usernameresult;
			}

			/* fileupload field */
			if (check_element.name.indexOf("[fileupload]") != -1 && check_element.className.match("required")) {
				var fileuploadresult = CheckFill(check_element);
				if (!fileuploadresult) {
					msg += getLabel(check_element).get('text')+': '+"<?php echo JText::_('COM_REDFORM_please_attach_a_file'); ?>\n";
				}
				if (result) result = fileuploadresult;
			}

			/* E-mail */
			if (check_element.name.indexOf("[email]") != -1 && check_element.className.match("required")) {
				if (CheckFill(check_element)) {
					if (!CheckEmail(check_element.value)) {
						msg = msg + "<?php echo JText::_('COM_REDFORM_No_valid_e-mail_address'); ?>\n";
						if (result) result = false;
					}
				}
				else {
					msg = msg + "<?php echo JText::_('COM_REDFORM_E-mail_address_is_empty'); ?>\n";
					if (result) result = false;
				}
			}

			/* multiselect field */
			if ((check_element.name.indexOf("[multiselect]") != -1 || check_element.name.indexOf("[select]") != -1)
					&& check_element.className.match("required")) {
				var multires = CheckFill(check_element);
				if (!multires) {
					msg += getLabel(check_element).get('text')+': '+"<?php echo JText::_('COM_REDFORM_select_a_value'); ?>\n";
				}
				if (result) result = multires;
			}

			/* Radio buttons */
			if (check_element.name.indexOf("[radio]") != -1 && check_element.className.match("required")) {
				radios = document.getElementsByName(check_element.name);
				var radiocheck = false;
				for (var rct=radios.length-1; rct > -1; rct--) {
					if (radios[rct].checked) {
						radiocheck = true;
						rct = -1;
					}
				}
				if (radiocheck == false) {
					document.id(check_element).getParent().getParent().addClass('emptyfield');
					getListLabel(check_element).addClass('emptyfield');
					radiomsg = getListLabel(check_element).get('text')+': '+"<?php echo JText::_('COM_REDFORM_JS_CHECK_FIELD_REQUIRED'); ?>\n";
					if (result) result = false;
				}
				else {
					document.id(check_element).getParent().getParent().removeClass('emptyfield');
					getListLabel(check_element).removeClass('emptyfield');
				}
			}

			/* Check boxes */
			if (check_element.name.indexOf("[checkbox]") != -1 && check_element.className.match("required")) {
				checkboxes = document.getElementsByName(check_element.name);
				var checkboxcheck = false;
				for (var rct=checkboxes.length-1; rct > -1; rct--) {
					if (checkboxes[rct].checked) {
						checkboxcheck = true;
						rct = -1;
					}
				}

				if (checkboxcheck == false) {
					document.id(check_element).getParent().getParent().addClass('emptyfield');
					getListLabel(check_element).addClass('emptyfield');
					checkboxmsg = getListLabel(check_element).get('text')+': '+"<?php echo JText::_('COM_REDFORM_JS_CHECK_FIELD_REQUIRED'); ?>\n";
					if (result) result = false;
				}
				else {
					document.id(check_element).getParent().getParent().removeClass('emptyfield');
					getListLabel(check_element).removeClass('emptyfield');
				}
			}
	}
	}
	if (result == false) {
		if (radiomsg)	msg+= radiomsg;
		if (checkboxmsg)	msg+= checkboxmsg;
		alert(msg);
		return false;
	}

	return result;
}

function addClass(element, value)
{
	if (!element.className) {
		element.className = value;
	} else {
		var newClassName = element.className;
		newClassName += " ";
		newClassName += value;
		element.className = newClassName;
	}
}

function CheckFill(element)
{
	if (!(document.id(element).getProperty('value'))) {
		addEmpty(element);
		return false;
	}
	else {
		removeEmpty(element);
		return true;
	}
}

function addEmpty(element) {
	document.id(element).addClass('emptyfield');
	document.id(element).addClass('emptyfield');
}

function removeEmpty(element) {
	document.id(element).removeClass('emptyfield');
	getLabel(element).removeClass('emptyfield');
}

function getLabel(element) {
	return document.getElement('label[for="'+element.id+'"]');
}

/**
 * for radio and checkbox, we can't use the id of the element directly
 */
function getListLabel(element) {
	var name = element.name.substr(0, element.name.indexOf('.'));
	return document.getElement('label[for="'+name+'"]');
}

function CheckEmail(str)
{
	/* Check if regular expressions are supported */
	var supported = 0;
	if (window.RegExp) {
		var tempStr = "a";
		var tempReg = new RegExp(tempStr);
		if (tempReg.test(tempStr)) supported = 1;
	}
	if (!supported) return (str.indexOf(".") > 2) && (str.indexOf("@") > 0);

	/* Regular expressions supported */
	var r1 = new RegExp("(@.*@)|(\\.\\.)|(@\\.)|(^\\.)");
	var r2 = new RegExp("^.+\\@(\\[?)[a-zA-Z0-9\\-\\.]+\\.([a-zA-Z]{2,4}|[0-9]{1,4})(\\]?)$");
	return (!r1.test(str) && r2.test(str));
}

function AddUser()
{
	//jQuery("div#submit_button").show();
	var curform = parseInt(document.getElement("input[name='curform']").getProperty('value'));
	var maxform = parseInt(document.getElement("input[name='multi']").getProperty('value'));
	if (curform >= maxform) {
		alert("<?php echo JText::_('COM_REDFORM_MAX_SIGNUP_REACHED'); ?>\n");
	}
	else {
		document.getElements("[id^='formfield']").each(function(el) {
			document.id(el).setStyle('display', 'none');
		});
		document.id("formfield"+curform).setStyle('display', 'block');
		new Element('a', {'href' : '#'}).set('text', '# '+(curform+1)).addEvent('click', function(ev) {
			ev.preventDefault();
			ShowSingleForm('div#formfield'+(curform+1));
		}).injectInside(document.id("signedusers"));
		new Element('br').injectInside(document.id("signedusers"));
		document.getElement("input[name='curform']").setProperty('value', curform+1);
	}
}

function ShowSingleForm(showform) {
	document.getElements("[id^='formfield']").each(function(el) {
		document.id(el).setStyle('display', 'none');
	});
	document.getElement(showform).setStyle('display', 'block');
}

function ShowAllUsers(showhide) {
	var curform = parseInt(document.getElement("input[name='curform']").getProperty('value'));
	document.getElements("[id^='formfield']").each(function(el, i) {
		if (i < curform) {
			if (showhide) document.id(el).setStyle('display', 'block');
			else if (!showhide) document.id(el).setStyle('display', 'none');
		}
		else {
			document.id(el).setStyle('display', 'none');
		}
	});
}
