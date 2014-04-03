window.addEvent('domready', function() {
	var link = document.id('pagsegurolink').getProperty('href');
	if (link) {
		window.location = link;
	}
});
