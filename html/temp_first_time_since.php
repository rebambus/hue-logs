<script type="text/javascript" src="http://momentjs.com/downloads/moment.js"></script>
<h1 class="bd-title">Temperature</h1>

<?php
require_once('mysqli_connect.php');

$sql = "
SELECT   t.sensor_id,
         sensors.description,
         t.temperature,
         UNIX_TIMESTAMP(IF(t.last_time_below < t.last_time_above, t.last_time_below, t.last_time_above)) AS last_time
FROM     (   SELECT temps.sensor_id,
                    temps.temperature,
                    temps.num_records,
                    (   SELECT MAX(lastupdated)
                        FROM   hue_sensor_data AS h
                        WHERE  h.sensor_id = temps.sensor_id
                            AND h.value >= temps.temperature) AS last_time_below,
                    (   SELECT MAX(lastupdated)
                        FROM   hue_sensor_data AS h
                        WHERE  h.sensor_id = temps.sensor_id
                            AND h.value <= temps.temperature) AS last_time_above
             FROM   (   SELECT   sensor_id, CAST(value AS SIGNED) AS temperature, COUNT(*) AS num_records
                        FROM     hue_sensor_data
                        WHERE    type = 'temperature'
                        GROUP BY sensor_id, CAST(value AS SIGNED)) AS temps ) AS t
         JOIN hue_sensors AS sensors
             ON sensors.sensor_id = t.sensor_id
ORDER BY t.sensor_id, t.temperature;
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
	echo '<div class="table-responsive">';
	echo '<table class="table table-striped table-sm">';
		echo "<thead>";
			echo "<tr>";
				echo "<th>Sensor</th>";
				echo "<th>Temp</th>";
				echo "<th>Last Time</th>";
				echo "<th></th>";
			echo "</tr>\n";
		echo "</thead>";
		echo "<tbody>";
			while ($row = $result->fetch_assoc()) {
				echo "<tr>";
					echo "<td>";
						echo '<a href="index.php?' . http_build_query(array_merge($_GET, array("page"=>"sensors","id"=>$row['sensor_id']))) . '">';
							echo $row['description'];
						echo'</a>';
					echo"</td>";
					echo "<td>" . $row["temperature"] . "</td>";
					echo '<td><span title="'. $row['last_time']. '"><script>document.write(moment.unix("'. $row['last_time']. '").calendar());</script></span></td>';
					echo '<td><span title="'. $row['last_time']. '" data-livestamp="'. $row['last_time']. '"></span></td>';
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