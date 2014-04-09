/**
 * @copyright Copyright (C) 2008, 2009, 2010, 2011 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

var RedformFormMultiple = new Class({
	initialize: function()
	{
		$$('.add-registration').addEvent('click', this.addRegistration.bind(this));
	},

	addRegistration : function(event)
	{
		var element = event.target;
		var maxform = parseInt(document.getElement("input[name='multi']").get('value'));
		var subforms = element.getParent('form').getElements('.formbox');
		var nbactive = subforms.length;
		var last = subforms.getLast();

		if (nbactive >= maxform)
		{
			alert(Joomla.JText._("COM_REDFORM_MAX_SIGNUP_REACHED"));
			return;
		}

		var newSubForm = last.clone();
		this.updateIndex(newSubForm, nbactive + 1);
		newSubForm.inject(last, 'after');

		if (redformPrice)
		{
			redformPrice.bindElements(newSubForm);
		}
	},

	updateIndex : function(subform, index)
	{
		subform.getElements('input, textarea, select').each(function(el) {
			var name = el.get('name').replace(/(field[0-9]+\.)([0-9+])/g, "$1" + index);

			if (!el.get('type') == 'hidden') {
				el.set('name', name).set('value', null);
			}
		});
		var legend = Joomla.JText._("COM_REDFORM_FIELDSET_SIGNUP_NB").replace(/(%d)/, index);
		subform.getElement('legend').set('text', legend);
	}
});

document.redformmultiple = null;
window.addEvent('domready', function(){
	document.redformmultiple = new RedformFormMultiple();
});

