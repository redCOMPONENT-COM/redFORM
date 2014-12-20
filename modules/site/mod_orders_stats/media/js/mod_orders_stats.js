// Load the Visualization API and the piechart package.
google.load('visualization', '1.0', {'packages':['corechart']});

// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(drawChart);

// Callback that creates and populates a data table,
// instantiates the pie chart, passes in the data and
// draws it.
function drawChart() {

	if (!companySales) {
		return;
	}

	var rows = [];

	for (var i = 0, n = companySales.length; i < n; i++) {
		var row = [companySales[i].name, companySales[i].gas + companySales[i].elec];
		rows.push(row);
	}

	// Create the data table.
	var sales = new google.visualization.DataTable();
	sales.addColumn('string', 'company');
	sales.addColumn('number', 'sales');

	sales.addRows(rows);

	// Set chart options
	var options = {'width':400,
		'height':200};

	// Instantiate and draw our chart, passing in some options.
	var chart = new google.visualization.PieChart(document.getElementById('sales_div'));
	chart.draw(sales, options);
}