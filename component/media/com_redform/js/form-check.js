/**
 * @copyright Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
window.addEvent('domready', function(){
	$$('.form-validate input[type=submit]').addEvent('click', function(){
		if (document.formvalidator.isValid(this.form)) {
			return true;
		}

		alert(Joomla.JText._('JGLOBAL_VALIDATION_FORM_FAILED'));
		return false;
	});
});
