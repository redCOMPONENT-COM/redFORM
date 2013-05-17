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

window.addEvent('domready', function(){
	$$('input[class^=score_]').addEvent('click', function(){
		var total = 0;
		$$('.' + this.get('class')).each(function(el){
			if (el.getProperty('checked')) {
				total += parseInt(el.get('value'));
			}
		});
		var group = this.get('class').substr(6);
		$$('.subtotal_' + group).set('value', total);
		rwf_formscore.updatetotal();
	});
});

var rwf_formscore = {
		updatetotal : function() {
			var total = 0;
			$$('input[class^=subtotal_]').each(function(el){
				total += parseInt(el.get('value'));
			});
			$$('.rwftotal').set('value', total);
		}
};
