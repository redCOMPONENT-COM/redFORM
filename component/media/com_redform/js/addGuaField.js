/**
 * @package     Redform
 *
 * @copyright   Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

window.addEvent('domready', function(){
	ga(function(tracker)
	{
		clientId = tracker.get('clientId');
		$('GuaClientId').set('value', clientId);
	});
});
