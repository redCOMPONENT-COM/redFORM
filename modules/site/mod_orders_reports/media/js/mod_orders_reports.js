// Load the Visualization API and the piechart package.
google.load('visualization', '1.0', {'packages':['corechart']});

// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(function() {

	if (monthStats) {
		var rows = [];

		rows.push([Joomla.JText._('MOD_ORDERS_STATS_GRAPH_LABEL_DAYOFTHEMONTH'),
			Joomla.JText._('MOD_ORDERS_STATS_GRAPH_LABEL_CANCELLED'),
			Joomla.JText._('MOD_ORDERS_STATS_GRAPH_LABEL_ERRORS'),
			Joomla.JText._('MOD_ORDERS_STATS_GRAPH_LABEL_REPORTS')]);

		for (var i = 0, n = monthStats.length; i < n; i++) {
			var row = [monthStats[i].day, monthStats[i].cancelled, monthStats[i].errors, monthStats[i].reports];
			rows.push(row);
		}

		var data = google.visualization.arrayToDataTable(rows);
		var chart = new google.visualization.LineChart(document.getElementById('error_month_div'));
		chart.draw(data);
	}

	if (yearStats) {
		var rows = [];

		rows.push([Joomla.JText._('MOD_ORDERS_STATS_GRAPH_LABEL_MONTH'),
			Joomla.JText._('MOD_ORDERS_STATS_GRAPH_LABEL_CANCELLED'),
			Joomla.JText._('MOD_ORDERS_STATS_GRAPH_LABEL_ERRORS'),
			Joomla.JText._('MOD_ORDERS_STATS_GRAPH_LABEL_REPORTS')]);

		for (var i = 0, n = yearStats.length; i < n; i++) {
			var row = [yearStats[i].month, yearStats[i].cancelled, yearStats[i].errors, yearStats[i].reports];
			rows.push(row);
		}

		var data = google.visualization.arrayToDataTable(rows);
		var chart = new google.visualization.LineChart(document.getElementById('error_year_div'));
		chart.draw(data);
	}
});