<?php include 'chart_calendar.php'?>

<div id="calendar_max_div" style="width: 100%; height: 400px;"></div>

<p>Links to chart data in
	<a href="chart_calendar.php?<?php echo http_build_query(array_merge($_GET, array("output"=>null)))?>">JSON</a>,
	<a href="chart_calendar.php?<?php echo http_build_query(array_merge($_GET, array("output"=>"sql")))?>">SQL</a>, or
	<a href="chart_calendar.php?<?php echo http_build_query(array_merge($_GET, array("output"=>"table")))?>">HTML table</a>.
</p>

<?php include 'time_since_script.php'?>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
	google.charts.load("current", {packages:["calendar"]});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {
		var dataTable = new google.visualization.DataTable(<?php temp_chart_data();?>);

		var options = {
			title: 'Hottest',
			hAxis: {
				curveType: 'function',
				title: 'Date'
			},
			vAxis: {
				title: 'Temperature'
			}
		};

		var chart = new google.visualization.Calendar(document.getElementById('calendar_max_div'));

		chart.draw(dataTable, options);
	}
</script>
