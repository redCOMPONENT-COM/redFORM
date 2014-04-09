/**
 * @copyright Copyright (C) 2008, 2009, 2010, 2011 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

var RedformFormMultiple = new Class({
	initialize: function()
	{
		$$('.add-instance').addEvent('click', this.addInstance.bind(this));
	},

	addInstance : function(event)
	{
		var element = event.target;
		var form = element.getParent('form');
		var maxform = parseInt(form.getElement("input[name='multi']").get('value'));
		var subforms = form.getElements('.formbox');
		var nbactive = subforms.length;
		var last = subforms.getLast();

		if (nbactive >= maxform)
		{
			alert(Joomla.JText._("COM_REDFORM_MAX_SIGNUP_REACHED"));
			return;
		}

		var newSubForm = last.clone();
		this.updateIndex(newSubForm, nbactive + 1, true);
		newSubForm.inject(last, 'after');

		// Update count of active
		form.getElement("input[name='nbactive']").set('value', nbactive + 1);

		// trigger price update
		if (redformPrice)
		{
			redformPrice.bindElements(newSubForm);
		}
	},

	removeInstance : function(event) {
		var removeIndex = event.target.get('index');
		var form = event.target.getParent('form');

		// Remove this instance
		event.target.getParent('.formbox').dispose();

		// Shift all others
		var subforms = form.getElements('.formbox');

		for (var i = removeIndex - 1; i < subforms.length; i++) {
			this.updateIndex(subforms[i], i+1, false)
		}

		// Update count of active
		form.getElement("input[name='nbactive']").set('value', subforms.length);

		// trigger price update
		if (redformPrice)
		{
			redformPrice.bindElements(form);
		}
	},

	updateIndex : function(subform, index, resetValue)
	{
		subform.getElements('input, textarea, select').each(function(el) {
			var name = el.get('name').replace(/(field[0-9]+\.)([0-9+])/g, "$1" + index);
			el.set('name', name);

			if (resetValue && el.get('type') != 'hidden') {
				el.set('value', null);
			}
		});

		var legend = subform.getElement('legend').empty();
		var signupTitle = Joomla.JText._("COM_REDFORM_FIELDSET_SIGNUP_NB").replace(/(%d)/, index);
		var deleteLink = new Element('span', {'class' : 'remove-instance', 'index' : index}).set('text', 'remove')
			.addEvent('click', this.removeInstance.bind(this));

		legend.appendText(signupTitle + ' - ');
		legend.adopt(deleteLink);
	}

});

document.redformmultiple = null;
window.addEvent('domready', function(){
	document.redformmultiple = new RedformFormMultiple();
});

