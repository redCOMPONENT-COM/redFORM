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
        e.stop();
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
	var type = document.id('fieldtype').value;
	var id   = document.id('fieldid').value;
	
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
		document.id('field-options').setStyle('display', 'block');
	}
	else {
		document.id('field-options').setStyle('display', 'none');
	}
	var url = 'index.php?option=com_redform&view=field&format=raw&cid[]='+id+'&layout=values';
	//alert(url);
	
	var theAjax = new Request({
		url:		url,
		method: 	'POST',
		onSuccess: function(response)
			{
				//alert('yes');
				var rows = document.id('values-rows');
				
				var values = eval('(' + response + ')');
				values.each(function(el){
					if(document.id('value-'+el.id) != null){
						document.id('value-'+el.id).dispose();
					}
					newRow(el).inject(rows);
					
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
	var test = '';
	test = getUrlVars(url);
	if((document.id('value-'+test['cid[]']) != null) && test['task'] == "ajaxremove")
	{
		document.id('value-'+test['cid[]']).dispose();
	};
	var theAjax = new Request({
		url:		url,
		method: 	'POST',
		onSuccess: function(response)
			{
				update_values();
			}
		});
	theAjax.send();
}

function newRow(value) 
{
	var fieldid   = document.id('fieldid').value;

	var tr = new Element('tr', {'id': 'value-'+value.id, 'class': 'value-details'});
	// value
	new Element('td').appendText(value.value).inject(tr);
	// label
	new Element('td').appendText(value.label).inject(tr);
	// published
	new Element('td').appendText(value.price).inject(tr);
	if (value.published == 1) {
		new Element('img', {'src': redformmedia+'/images/ok.png', 'style': 'cursor:pointer;', 'alt': textyes, events: {click: function(){ ajaxgetandupdate('index.php?option=com_redform&controller=values&task=ajaxunpublish&tmpl=component&cid[]='+value.id);}}})
			.inject(new Element('td').inject(tr));
	}
	else {
		new Element('img', {'src': redformmedia+'/images/no.png', 'style': 'cursor:pointer;', 'alt': textno, events: {click: function(){ ajaxgetandupdate('index.php?option=com_redform&controller=values&task=ajaxpublish&tmpl=component&cid[]='+value.id);}}})
		.inject(new Element('td').inject(tr));
	}  
	// up/down links
	var tdlink = new Element('td').inject(tr);
	var upurl  = 'index.php?option=com_redform&controller=values&task=ajaxorderup&tmpl=component&cid[]='+value.id+'&fieldid='+fieldid;
	var downurl = 'index.php?option=com_redform&controller=values&task=ajaxorderdown&tmpl=component&cid[]='+value.id+'&fieldid='+fieldid;
	new Element('img',{'src': redformmedia+'/images/uparrow.png', 'style': 'cursor:pointer;', 'alt': textup, 'class': "uparrow" , events: {click: function(){ajaxgetandupdate(upurl);}}})
		.inject(tdlink);
	new Element('img',{'src': redformmedia+'/images/downarrow.png', 'style': 'cursor:pointer;', 'alt': textdown, 'class': "downarrow" , events: {click: function(){ajaxgetandupdate(downurl);}}})
		.inject(tdlink);
	new Element('span').set('text', value.ordering).inject(tdlink);
	// edit link
	var tdlink = new Element('td').inject(tr);
	new Element('a', {'href': 'index.php?option=com_redform&controller=values&task=ajaxedit&tmpl=component&cid[]='+value.id+'&fieldid='+fieldid})
	.appendText(edittext).inject(tdlink).addEvent('click', function(e) {
		e.stop();
		SqueezeBox.fromElement(this);
	});
	// remove link
	var tddelete = new Element('td', {'class': 'cell-delvalue'}).inject(tr);
	var link = 'index.php?option=com_redform&controller=values&task=ajaxremove&tmpl=component&cid[]='+value.id+'&fieldid='+fieldid;
	new Element('a', {'href': '#', events: {click: function(){ajaxgetandupdate(link);}}})
	.appendText(deletetext).inject(tddelete).addEvent('click', function(e) {
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