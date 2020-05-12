<script type="text/javascript" src="http://momentjs.com/downloads/moment.js"></script>
<?php
require_once('mysqli_connect.php');

if (isset($_GET['id'])) {
// history for one sensor
  $sql = "
SELECT sensor.sensor_id,
  	sensor.description,
  	UNIX_TIMESTAMP(log.lastupdated) lastupdated,
  	log.type,
  	CASE WHEN log.type = 'lightlevel' THEN CONCAT(ROUND(100*CAST(log.value AS UNSIGNED)/30000,0),' %')
  		WHEN log.type = 'temperature' THEN CONCAT(ROUND(log.value,1), ' °F')
  		WHEN log.type = 'motion' AND value = '1' THEN CONCAT('Activity for ',(SELECT MIN(next.lastupdated) FROM hue_sensor_data next WHERE next.sensor_id = log.sensor_id AND next.lastupdated > log.lastupdated))
  		WHEN log.type = 'motion' AND value = '0' THEN CONCAT('No Activity for ',(SELECT MIN(next.lastupdated) FROM hue_sensor_data next WHERE next.sensor_id = log.sensor_id AND next.lastupdated > log.lastupdated))
  		ELSE log.value
  	END value
  FROM hue_sensor_data log
  JOIN hue_sensors sensor ON sensor.sensor_id = log.sensor_id
  WHERE sensor.sensor_id = " . $_GET['id'] . "
  	AND NOT (log.type = 'motion' AND log.value = '0')
  ORDER BY log.lastupdated DESC
  LIMIT 10;
  ";

      $result = $conn->query($sql);
      $row = $result->fetch_assoc();
      $result->data_seek(0); // Just tickle the first row, then rewind.
      echo '<h1 class="h2">' . $row['description'] . ' (' . $row['type'] . ')</h1>';
?>

<p><a href="http://192.168.86.172/api/E7aAajMAh3Uz5U39V2rCNuxuBmA3CJZVy31bF7rc/sensors/<?php echo $_GET['id']; ?>">API</a></p>

<p>View data for the last
	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"1")))?>">hour</a>,
	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"6")))?>">6 hours</a>,
	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"24")))?>">day</a>,
	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"168")))?>">week</a>, or
	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"720")))?>">month</a>.
</p>

<div id="chart_div" class="img-fluid"></div> <!--style="width: 100%; height: 400px;"-->

<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Time</th>
                <th>Time Ago</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            <?php
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td><span title="'. $row['lastupdated']. '"><script>document.write(moment.unix("'. $row['lastupdated']. '").local().calendar());</script></span></td>';
                    echo '<td><script>document.write(moment.unix("'. $row['lastupdated']. '").fromNow());</script></td>';
                    echo '<td>'. $row['value']. '</td>';
                    echo '</tr>';
                }
            ?>
        </tbody>
    </table>
</div>


<?php
} // end of if (isset($_GET['id']))
else {
    echo '<h1 class="h2">Sensors</h1>';
}

// list of all sensors
$sql = "
SELECT sensor.sensor_id,
	sensor.description,
	UNIX_TIMESTAMP(log.lastupdated) lastupdated,
	log.type,
	CASE
		WHEN log.type = 'lightlevel' THEN CONCAT(ROUND(100*CAST(log.value AS UNSIGNED)/30000,0),' %')
		WHEN log.type = 'temperature' THEN CONCAT(ROUND(value,1), ' °F')
		WHEN log.type = 'motion' AND log.value = 1 THEN 'Activity'
		WHEN log.type = 'motion' AND log.value = 0 THEN 'No activity'
		ELSE log.value
		END AS value,
	CASE
		WHEN log.type = 'motion' AND log.value = 1 THEN '<span data-feather=''users''></span>'
		WHEN log.type = 'lightlevel' AND log.value > 10000 THEN '<span data-feather=''sun''></span>'
		WHEN log.type = 'lightlevel' AND log.value = 0 THEN '<span data-feather=''moon''></span>'
		WHEN log.type = 'temperature' AND log.value < 40 THEN '<span data-feather=''trending-down''></span>'
		WHEN log.type = 'temperature' AND log.value > 75 THEN '<span data-feather=''trending-up''></span>'
		ELSE ''
		END AS feather
FROM (SELECT sensor_id, MAX(lastupdated) lastupdated FROM hue_sensor_data GROUP BY sensor_id) last_update
JOIN hue_sensor_data log ON log.sensor_id = last_update.sensor_id AND log.lastupdated = last_update.lastupdated
JOIN hue_sensors sensor ON sensor.sensor_id = log.sensor_id
WHERE log.lastupdated IS NOT NULL
ORDER BY CASE log.type WHEN 'temperature' THEN 1 WHEN 'lightlevel' THEN 2 WHEN 'motion' THEN 3 ELSE 4 END, log.value DESC;
";

$result = $conn->query($sql);
?>

<div class="table-responsive">
	<table id="sensor-table" class="table table-striped table-sm">
		<thead>
			<tr>
				<th>Sensor</th>
				<th>Icon</th>
				<th>Value</th>
				<th>Type</th>
				<th>Last Update</th>
				<th>Time Ago</th>
			</tr>
		</thead>
		<tbody>
			<?php
			while ($row = $result->fetch_assoc()) {
				echo '<tr>';
					echo '<td><a href="index.php?' . http_build_query(array_merge($_GET, array("id"=>$row['sensor_id']))) . '">'. $row['description'] . '</a></td>';
					echo '<td>'. $row['feather']. '</td>';
					echo '<td>'. $row['value']. '</td>';
					echo '<td>'. $row['type']. '</td>';
					echo '<td><span title="'. $row['lastupdated']. '"><script>document.write(moment.unix("'. $row['lastupdated']. '").calendar());</script></span></td>';
					echo '<td><span title="'. $row['lastupdated']. '" data-livestamp="'. $row['lastupdated']. '"></span></td>';
				echo '</tr>';
			}
			?>
		</tbody>
	</table>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
	google.charts.load('current', {
		'packages': ['corechart']
	});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {
		var data = google.visualization.arrayToDataTable([
			<?php
                require_once('mysqli_connect.php');

                $sql = "
                SELECT CONCAT('[',
                	'new Date(', 1000*UNIX_TIMESTAMP(lastupdated), '),',
                	IFNULL(CASE WHEN type = 'lightlevel' THEN 100*CAST(value AS UNSIGNED)/30000 ELSE value END,'null'), ',',
                  '],') chart_row -- list with 2 elements
                FROM hue_sensor_data log
                WHERE log.sensor_id = ". $_GET['id']. "
                	AND log.lastupdated > TIMESTAMPADD(HOUR,-@hours_back,UTC_TIMESTAMP())
                ORDER BY log.id DESC
                LIMIT 10000;
                ";

                if (!isset($_GET['hours_back'])) {
                	$hours_back = 24;
                } else {
                	$hours_back = $_GET['hours_back'];
                }
                $sql = str_replace("@hours_back", $hours_back, $sql);

                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    echo "['Time','Value'],";
                    while ($row = $result->fetch_assoc()) {
                        echo $row["chart_row"];
                    }
                } else {
                }
            ?>
		]);

		var options = {curveType: 'function',legend: {position: 'none'}};
		var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
		chart.draw(data, options);
	}
</script>

