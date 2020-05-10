<h1 class="h2">Light Level</h1>

<?php include 'time_since_script.php'?>
<p>Data from <span id="time_elapsed"><?php echo date("Y-m-d h:i:s a");?></span>.</p>
<p>View data for the last
	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"1")))?>">hour</a>,
	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"6")))?>">6 hours</a>,
	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"24")))?>">day</a>,
	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"168")))?>">week</a>, or
	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"720")))?>">month</a>.
</p>

<div id="chart_div" style="width: 100%; height: 400px;"></div>

<p>Links to chart data in
	<a href="chart_lightlevel.php?<?php echo http_build_query(array_merge($_GET, array("output"=>null)))?>">JSON</a>,
	<a href="chart_lightlevel.php?<?php echo http_build_query(array_merge($_GET, array("output"=>"sql")))?>">SQL</a>, or
	<a href="chart_lightlevel.php?<?php echo http_build_query(array_merge($_GET, array("output"=>"table")))?>">HTML table</a>.
</p>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
	google.charts.load('current', {'packages': ['corechart']});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {
		var dataTable = new google.visualization.DataTable(<?php include 'chart_lightlevel.php'?>);

		var options = {
			title: 'Hue Light Level',
			hAxis: {
				title: 'Timestamp'
			},
			vAxis: {
				title: 'Light Level'
			}
		};

		var chart = new google.visualization.LineChart(document.getElementById('chart_div'));

		chart.draw(dataTable, options);
	}
</script>