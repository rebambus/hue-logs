<?php
require_once('mysqli_connect.php');

$sql = "
SELECT   sensor.sensor_id,
         sensor.description,
         ROUND(log.value, 1) AS temperature,
         UNIX_TIMESTAMP(log.lastupdated) AS time,
         ROUND(log_min.value, 1) AS min_temp,
         UNIX_TIMESTAMP(log_min.lastupdated) AS min_time,
         ROUND(log_max.value, 1) AS max_temp,
         UNIX_TIMESTAMP(log_max.lastupdated) AS max_time
FROM     hue_sensor_data AS log
         JOIN hue_sensors AS sensor
             ON sensor.sensor_id = log.sensor_id
         LEFT JOIN hue_sensor_data AS log_min
             ON log_min.id = (   SELECT   id
                                 FROM     hue_sensor_data AS h2
                                 WHERE    h2.sensor_id = log.sensor_id
                                     AND lastupdated >= timestampadd(DAY, -1, UTC_TIMESTAMP())
                                 ORDER BY value, id DESC
                                 LIMIT 1
             )
         LEFT JOIN hue_sensor_data log_max
             ON log_max.id = (   SELECT   id
                                 FROM     hue_sensor_data AS h2
                                 WHERE    h2.sensor_id = log.sensor_id
                                     AND lastupdated >= timestampadd(DAY, -1, UTC_TIMESTAMP())
                                 ORDER BY value DESC, id DESC
                                 LIMIT 1
             )
WHERE    log.type= 'temperature'
    AND log.id IN( SELECT MAX(id)FROM hue_sensor_data GROUP BY sensor_id )
ORDER BY CASE WHEN description LIKE '%porch%'
                  THEN 1
              ELSE 2
         END,
         sensor.description;
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
	echo '<div class="table-responsive">';
	echo '<table class="table table-striped table-sm">';
		echo "<thead>";
			echo "<tr>";
				echo "<th>Sensor</th>";
				echo "<th>Temp</th>";
				echo "<th>Time</th>";
				echo "<th>Time Ago</th>";
				echo "<th>24 hr min</th>";
				echo "<th></th>";
				echo "<th></th>";
				echo "<th>24 hr max</th>";
				echo "<th></th>";
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
				echo '<td><span title="'. $row['time']. '"><script>document.write(moment.unix("'. $row['time']. '").calendar());</script></span></td>';
				echo '<td><span title="'. $row['time']. '" data-livestamp="'. $row['time']. '"></span></td>';
				echo "<td>" . $row["min_temp"] . "</td>";
				echo '<td><span title="'. $row['min_time']. '"><script>document.write(moment.unix("'. $row['min_time']. '").calendar());</script></span></td>';
				echo '<td><span title="'. $row['min_time']. '" data-livestamp="'. $row['min_time']. '"></span></td>';
				echo "<td>" . $row["max_temp"] . "</td>";
				echo '<td><span title="'. $row['max_time']. '"><script>document.write(moment.unix("'. $row['max_time']. '").calendar());</script></span></td>';
				echo '<td><span title="'. $row['max_time']. '" data-livestamp="'. $row['max_time']. '"></span></td>';
				echo "</tr>\n";
			}
		echo "</tbody>";
	echo "</table>";
	echo "</div>";
} else {
	echo "0 results";
}
?>
