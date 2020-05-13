<script type="text/javascript" src="http://momentjs.com/downloads/moment.js"></script>
<h1 class="bd-title">Timeline</h1>

<?php
require_once('mysqli_connect.php');

$sql = "
SELECT   UNIX_TIMESTAMP(t.time) AS time, t.description, t.feather
FROM     (   SELECT lastupdated AS time,
                    CONCAT(CASE WHEN value = 1
                                    THEN 'Motion detected'
                                WHEN value = 0
                                    THEN 'No motion detected'
                                ELSE 'Unknown?'
                           END,
                           ' in ',
                           sensor.description) AS description,
                    CASE WHEN value = 1
                             THEN '<span data-feather=''users''></span>'
                         ELSE ''
                    END AS feather
             FROM   hue_sensor_data AS l
                    JOIN hue_sensors AS sensor
                        ON sensor.sensor_id = l.sensor_id
             WHERE  type = 'motion' AND value = 1
             UNION ALL
             SELECT start_time AS time,
                    CONCAT(
                        light.description,
                        ' turned ',
                        CASE WHEN reachable = 0
                                 THEN 'unreachable'
                             WHEN state = 1
                                 THEN 'on'
                             WHEN state = 0
                                 THEN 'off'
                             ELSE 'unknown?'
                        END,
                        ' for ',
                        '<script>document.write(moment.duration(',
                        TIMESTAMPDIFF(SECOND, start_time, end_time),
                        ',''seconds'').humanize());</script>') AS description,
                    CASE WHEN STATE = 1
                             THEN '<span data-feather=''sun''></span>'
                         ELSE ''
                    END feather
             FROM   light_history AS l
                    JOIN lights AS light
                        ON light.id = l.light_id
             WHERE  l.state = 1) t
ORDER BY t.TIME DESC
LIMIT 100;
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
	echo '<div class="table-responsive">';
	echo '<table class="table table-striped table-sm">';
		echo "<thead>";
			echo "<tr>";
				echo "<th>Time</th>";
				echo "<th></th>";
				echo "<th></th>";
				echo "<th></th>";
			echo "</tr>\n";
		echo "</thead>";
		echo "<tbody>";
			while ($row = $result->fetch_assoc()) {
				echo "<tr>";
					echo '<td><span title="'. $row['time']. '"><script>document.write(moment.unix("'. $row['time']. '").calendar());</script></span></td>';
					echo '<td><span title="'. $row['time']. '" data-livestamp="'. $row['time']. '"></span></td>';
					echo "<td>" . $row["feather"] . "</td>";
					echo "<td>" . $row["description"] . "</td>";
				echo "</tr>\n";
			}
		echo "</tbody>";
	echo "</table>";
	echo "</div>";
} else {
	echo "0 results";
}
?>

<p><a href="http://192.168.86.172/api/E7aAajMAh3Uz5U39V2rCNuxuBmA3CJZVy31bF7rc">Direct link to Hue API call (local only).</a></p>

<?php include 'time_since_script.php'?>

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