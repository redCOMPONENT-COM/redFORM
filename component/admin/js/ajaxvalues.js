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
	SqueezeBox.initialize({handler: 'iframe', size: {x: 600, y: 500}});
    $$('a.valuemodal').each(function(el) {
      el.addEvent('click', function(e) {
        new Event(e).stop();
        SqueezeBox.fromElement(el);
      });
    });
    
	update_values();
});
	
function newvalue()
{
	window.parent.SqueezeBox.close();
	update_values();
}

// update values
function update_values()
{
	var type = $('fieldtype').value;
	var id   = $('fieldid').value;
	
	if (!parseInt(id)) {
		return false;
	}
	
	if ( type == 'select' 
	  || type == 'multiselect'
      || type == 'radio'
      || type == 'checkbox'
      || type == 'price'
      || type == 'info'
      || type == 'recipients'
      //|| type == 'email'
	) {
		$('field-options').setStyle('display', 'block');
	}
	else {
		$('field-options').setStyle('display', 'none');
	}
	var url = 'index.php?option=com_redform&view=field&format=raw&cid[]='+id+'&layout=values';
	//alert(url);
	
	var theAjax = new Request({
		url:		url,
		method: 	'POST',
		onSuccess: function(response)
			{
				//alert('yes');
				var rows = $('values-rows');
				
				var values = eval('(' + response + ')');
				values.each(function(el){
					if(document.id('value-'+el.id) != null){
						$('value-'+el.id).dispose();
					}
					newRow(el).injectInside(rows);
					
				});
			},
		onFailure: function(response)
			{
				alert('no');
			},
		});
	theAjax.send();
}

function ajaxgetandupdate(url)
{
	var postStr  = '';
	var test = '';
	test = getUrlVars(url);
	if((document.id('value-'+test['cid[]']) != null) && test['task'] == "ajaxremove")
	{
		$('value-'+test['cid[]']).dispose();
	};
	var theAjax = new Request({
		url:		url,
		method: 	'POST',
		onSuccess: function(response)
			{
				update_values();
			}
		})
	theAjax.send();
}

function newRow(value) 
{
	var fieldid   = $('fieldid').value;

	var tr = new Element('tr', {'id': 'value-'+value.id, 'class': 'value-details'});
	// value
	new Element('td').appendText(value.value).injectInside(tr);
	// label
	new Element('td').appendText(value.label).injectInside(tr);
	// published
	new Element('td').appendText(value.price).injectInside(tr);
	if (value.published == 1) {
		new Element('img', {'src': 'http://'+document.location.host+'/administrator/templates/bluestork/images/admin/tick.png', 'style': 'cursor:pointer;', 'alt': textyes, events: {click: function(){ ajaxgetandupdate('index.php?option=com_redform&controller=values&task=ajaxunpublish&tmpl=component&cid[]='+value.id);}}})
			.injectInside(new Element('td').injectInside(tr));
	}
	else {
		new Element('img', {'src': 'http://'+document.location.host+'/administrator/templates/bluestork/images/admin/publish_x.png', 'style': 'cursor:pointer;', 'alt': textno, events: {click: function(){ ajaxgetandupdate('index.php?option=com_redform&controller=values&task=ajaxpublish&tmpl=component&cid[]='+value.id);}}})
		.injectInside(new Element('td').injectInside(tr));
	}  
	// up/down links
	var tdlink = new Element('td').injectInside(tr);
	var upurl  = 'index.php?option=com_redform&controller=values&task=ajaxorderup&tmpl=component&cid[]='+value.id+'&fieldid='+fieldid;
	var downurl = 'index.php?option=com_redform&controller=values&task=ajaxorderdown&tmpl=component&cid[]='+value.id+'&fieldid='+fieldid;
	var classname = "upactive";
	new Element('div', { 'alt': textup, 'class': classname , events: {click: function(){ajaxgetandupdate(upurl);}}})
		.injectInside(tdlink).addEvent('click', function(e) {
			ajaxgetandupdate(upurl);
		});
	var classname = "downactive";
	new Element('div', { 'alt': textdown, 'class': classname , events: {click: function(){ajaxgetandupdate(downurl);}}})
		.injectInside(tdlink).addEvent('click', function(e) {
			ajaxgetandupdate(downurl);
		});
	// edit link
	var tdlink = new Element('td').injectInside(tr);
	new Element('a', {'href': 'index.php?option=com_redform&controller=values&task=ajaxedit&tmpl=component&cid[]='+value.id+'&fieldid='+fieldid})
	.appendText(edittext).injectInside(tdlink).addEvent('click', function(e) {
		new Event(e).stop();
		SqueezeBox.fromElement(this);
	});
	// remove link
	var tddelete = new Element('td', {'class': 'cell-delvalue'}).injectInside(tr);
	var link = 'index.php?option=com_redform&controller=values&task=ajaxremove&tmpl=component&cid[]='+value.id+'&fieldid='+fieldid;
	new Element('a', {'href': '#', events: {click: function(){ajaxgetandupdate(link);}}})
	.appendText(deletetext).injectInside(tddelete).addEvent('click', function(e) {
		ajaxgetandupdate(link);
	});
	return tr;
	

}

function getUrlVars(url)
{
    var vars = [], hash;
    var hashes = url.slice(url.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}