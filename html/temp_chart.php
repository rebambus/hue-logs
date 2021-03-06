<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<script type="text/javascript" src="http://momentjs.com/downloads/moment.js"></script>
<h1 class="bd-title">Temperature</h1>

<p>View data for the last
	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"1")))?>">hour</a>,
	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"6")))?>">6 hours</a>,
	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"24")))?>">day</a>,
	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"168")))?>">week</a>, or
	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"720")))?>">month</a>.
</p>

<!-- <div class="my-4 w-100" id="chart_div" width="900" height="900"></div> -->
<div id="chart_div" style="width: 100%; height: 400px;"></div>

<p>Links to chart data in
	<a href="chart_temperature.php?<?php echo http_build_query(array_merge($_GET, array("output"=>null)))?>">JSON</a>,
	<a href="chart_temperature.php?<?php echo http_build_query(array_merge($_GET, array("output"=>"sql")))?>">SQL</a>, or
	<a href="chart_temperature.php?<?php echo http_build_query(array_merge($_GET, array("output"=>"table")))?>">HTML table</a>.
</p>

<p><a href="http://192.168.86.172/api/E7aAajMAh3Uz5U39V2rCNuxuBmA3CJZVy31bF7rc">Direct link to Hue API call (local only).</a></p>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
	google.charts.load('current', {'packages': ['corechart']});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {
		var dataTable = new google.visualization.DataTable(<?php include 'chart_temperature.php'?>);

		var options = {
            title: 'Recent Temperatures',curveType: 'function',
            hAxis: {title: 'Date and Time'},vAxis: {title: 'Temperature'}};

		var chart = new google.visualization.LineChart(document.getElementById('chart_div'));

		chart.draw(dataTable, options);
	}
</script>