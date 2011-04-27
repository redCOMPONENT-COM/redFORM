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
	var newclass = 'emptyfield';
	var checkboxmsg = false;
	var radiomsg = false;

	// only check the form that were activated by the user
	var forms = jQuery('.formbox');
	var nb_active = parseInt(jQuery("input[name='curform']").val());

	for (var j = 0 ; j < nb_active ; j++)
	{
		// get the input data of the form
		var formelements = jQuery(forms[j]).find(':input');

	for(i=0; i < formelements.length; i++) 
	{
		var check_element = formelements[i];

		/* Check field type */
		/* Fullname */
		if (check_element.name.indexOf("[fullname]") != -1 && check_element.className.match("required")) {
			var fullresult = CheckFill(check_element);
			if (!fullresult) {
					msg += getLabel(check_element).text()+': '+"<?php echo JText::_('please enter a name'); ?>\n";
			}
			if (result) result = fullresult;
		}
		
		/* Text field */
		if (check_element.name.indexOf("[text]") != -1 && check_element.className.match("required")) {
			var textresult = CheckFill(check_element);
			if (!textresult) {
					msg += getLabel(check_element).text()+': '+"<?php echo JText::_('COM_REDFORM_JS_CHECK_FIELD_REQUIRED'); ?>\n";
			}
			if (result) result = textresult;
		}
		
		/* Textarea field */
		if (check_element.name.indexOf("[textarea]") != -1 && check_element.className.match("required")) {
			var textarearesult = CheckFill(check_element);
			if (!textarearesult) {
					msg += getLabel(check_element).text()+': '+"<?php echo JText::_('COM_REDFORM_JS_CHECK_FIELD_REQUIRED'); ?>\n";
			}
			if (result) result = textarearesult;
		}
		
		/* Username field */
		if (check_element.name.indexOf("[username]") != -1 && check_element.className.match("required")) {
			var usernameresult = CheckFill(check_element);
			if (!usernameresult) {
					msg += getLabel(check_element).text()+': '+"<?php echo JText::_('please enter an username'); ?>\n";
			}
			if (result) result = usernameresult;
		}
		
		/* E-mail */
		if (check_element.name.indexOf("[email]") != -1 && check_element.className.match("required")) {
			if (CheckFill(check_element)) {
				if (!CheckEmail(check_element.value)) {
					msg = msg + "<?php echo JText::_('No valid e-mail address'); ?>\n";
					if (result) result = false;
				}
			}
			else {
				msg = msg + "<?php echo JText::_('E-mail address is empty'); ?>\n";
				if (result) result = false;
			}
		}

		/* multiselect field */
		if ((check_element.name.indexOf("[multiselect]") != -1 || check_element.name.indexOf("[select]") != -1) 
				&& check_element.className.match("required")) {
			var multires = CheckFill(check_element);
			if (!multires) {
					msg += getLabel(check_element).text()+': '+"<?php echo JText::_('select a value'); ?>\n";
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
    	jQuery(check_element).parents('.fieldoptions').addClass('emptyfield');
					getListLabel(check_element).addClass('emptyfield');
					radiomsg = getListLabel(check_element).text()+': '+"<?php echo JText::_('COM_REDFORM_JS_CHECK_FIELD_REQUIRED'); ?>\n";
      if (result) result = false;
    }
    else {
    	jQuery(check_element).parents('.fieldoptions').removeClass('emptyfield');
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
    	jQuery(check_element).parents('.fieldoptions').addClass('emptyfield');
    	getListLabel(check_element).addClass('emptyfield');
					checkboxmsg = getListLabel(check_element).text()+': '+"<?php echo JText::_('COM_REDFORM_JS_CHECK_FIELD_REQUIRED'); ?>\n";
      if (result) result = false;
    }
    else {
    	jQuery(check_element).parents('.fieldoptions').removeClass('emptyfield');
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
	if (!(jQuery(element).val())) {
		addEmpty(element);
		return false;
	}
	else {
		removeEmpty(element);
		return true;
	}
}

function addEmpty(element) {
	jQuery(element).addClass('emptyfield');
	getLabel(element).addClass('emptyfield');
}

function removeEmpty(element) {
	jQuery(element).removeClass('emptyfield');
	getLabel(element).removeClass('emptyfield');
}

function getLabel(element) {
	return jQuery('label[for="'+element.id+'"]');
}

/**
 * for radio and checkbox, we can't use the id of the element directly
 */
function getListLabel(element) {
	var name = element.name.substr(0, element.name.indexOf('.'));
	return jQuery('label[for="'+name+'"]');
}

function Trim(text) 
{
	text.value = jQuery.trim(text.value);
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
	var curform = parseInt(jQuery("input[name='curform']").val());
	var maxform = parseInt(jQuery("input[name='multi']").val());
	if (curform >= maxform) {
		alert("<?php echo JText::_('MAX_SIGNUP_REACHED'); ?>\n");
	}
	else {
		jQuery("[id^='formfield']").each(function(i) {
			jQuery(this).hide();
		});
		jQuery("#formfield"+curform).show();
		userlink = '<a href="#" onclick="ShowSingleForm(\'div#formfield'+(curform+1)+'\'); return false;"># '+(curform+1)+'</a><br />';
		jQuery("div#signedusers").append(userlink);
		jQuery("input[name='curform']").val(curform+1);
	}
	updatePrice();
}

function ShowSingleForm(showform) {
	jQuery("[id^='formfield']").each(function(i) {
		jQuery(this).hide();
	});
	jQuery(showform).show();
}

function ShowAllUsers(showhide) {
	var curform = parseInt(jQuery("input[name='curform']").val());
	jQuery("[id^='formfield']").each(function(i) {
		if (i < curform) {
			if (showhide) jQuery(this).show();
			else if (!showhide) jQuery(this).hide();
		}
		else {
			jQuery(this).hide();
		}
	});
}