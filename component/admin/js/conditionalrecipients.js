/**
 * @copyright Copyright (C) 2008, 2009, 2010, 2011 redCOMPONENT.com. All rights reserved. 
* @license	GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * javascript for dependant element xml parameter
 * 
 * 
 */
// add update of field when fields it depends on change.
window.addEvent('domready', function() {
	document.id('cr_function').addEvent('change', function(){
		var span = document.id("cr_params");
		span.empty();
		if (this.value == 'between') {
			new Element('input', {type: 'text', name: 'cr_param1', id: 'cr_param1', class: 'cr_param', 'size': 10}).injectInside(span);
			span.appendText(' ');
			new Element('input', {type: 'text', name: 'cr_param2', id: 'cr_param2', class: 'cr_param', 'size': 10}).injectInside(span);
		}
		if (this.value == 'superior' || this.value == 'inferior') {
			new Element('input', {type: 'text', name: 'cr_param1', id: 'cr_param1', class: 'cr_param', 'size': 10}).injectInside(span);
		}
	}).fireEvent('change');
	
	document.id('cr_button').addEvent('click', function(){
		var line = '';
		
		// reset error status
		document.id('cond_recipients_ui').getElements('input').each(function(element){
			element.removeClass('error');
		});
		document.id('cond_recipients_ui').getElements('select').each(function(element){
			element.removeClass('error');
		});
		
		if (!rfConditionalRecipient.checkEmail(document.id("cr_email").value)) {
			alert(emailrequired);
			document.id("cr_email").addClass('error');
			return false;
		}
		
		if (!document.id("cr_name").value) {
			alert(namerequired);
			document.id("cr_name").addClass('error');
			return false;
		}
		line += document.id("cr_email").value;
		line += ';'+document.id("cr_name").value;
		line += ';'+document.id("cr_field").value;
		line += ';'+document.id("cr_function").value;
		var check = true;
		$$('.cr_param').each(function(element){
			if (element.value === '') {
				element.addClass('error');
				check = false;
				return false;
			}
			line += ';'+element.value;
		});
		if (!check) {
			alert(missingparameter);
			return false;
		}
		document.id('cond_recipients').value += line+"\n";
	});
});
	
var rfConditionalRecipient = {
		checkEmail : function (value) {
			regex=/^[a-zA-Z0-9._-]+@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,4}$/;
			return regex.test(value);
		}
};