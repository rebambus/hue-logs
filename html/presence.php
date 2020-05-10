<html>
	<head>
		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
		<script type="text/javascript">
			google.charts.load('current', {'packages':['timeline']});
			google.charts.setOnLoadCallback(drawChart);
				function drawChart() {
				var container = document.getElementById('timeline');
				var chart = new google.visualization.Timeline(container);
				var dataTable = new google.visualization.DataTable(<?php include 'chart_presence.php'?>);
				chart.draw(dataTable);
			}
		</script>
		<?php include 'time_since_script.php'?>
	</head>
	<body>
		<p>Data from <span id="time_elapsed"><?php echo date("Y-m-d h:i:s a");?></span>.</p>
		<p>View data for the last
			<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"1")))?>">hour</a>,
			<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"6")))?>">6 hours</a>,
			<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"24")))?>">day</a>,
			<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"168")))?>">week</a>, or
			<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"720")))?>">month</a>.
		</p>
		<div id="timeline" style="height: 400px;"></div>
		<p>Links to chart data in
			<a href="chart_presence.php?<?php echo http_build_query(array_merge($_GET, array("output"=>null)))?>">JSON</a>,
			<a href="chart_presence.php?<?php echo http_build_query(array_merge($_GET, array("output"=>"sql")))?>">SQL</a>, or
			<a href="chart_presence.php?<?php echo http_build_query(array_merge($_GET, array("output"=>"table")))?>">HTML table</a>.
		</p>
	</body>
</html>
