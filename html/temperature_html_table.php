<?php
require_once('mysqli_connect.php');

$sql = "
SELECT	sensor.sensor_id,
	sensor.description sensor,
	ROUND(log.value,1) temperature,
	UNIX_TIMESTAMP(log.lastupdated) lastupdated,
	ROUND(min24,1) min24,
	ROUND(max24,1) max24,
	ROUND(max24-min24,1) delta24
FROM	hue_sensor_data log
JOIN	hue_sensors sensor ON sensor.sensor_id = log.sensor_id
LEFT JOIN	(SELECT
		sensor_id,
		MIN(value) min24,
		MAX(value) max24
	FROM hue_sensor_data
	WHERE type = 'temperature'
		AND lastupdated >= timestampadd(day,-1,UTC_TIMESTAMP())
	GROUP BY sensor_id
	) min_max ON min_max.sensor_id = log.sensor_id
WHERE	id IN (SELECT MAX(id) FROM hue_sensor_data GROUP BY sensor_id)
	AND type = 'temperature'
ORDER BY	CASE WHEN description LIKE '%porch%' THEN 1 ELSE 2 END;";

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
				echo "<th>24 hr max</th>";
				echo "<th>24 hr Δ</th>";
			echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
			while ($row = $result->fetch_assoc()) {
				echo "<tr>" .
				"<td>" . '<a href="index.php?' . http_build_query(array_merge($_GET, array("page"=>"sensors","id"=>$row['sensor_id']))) . '">'. $row['description'] .
					$row["sensor"] .  '</a>' . "</td>" .
				"<td>" . $row["temperature"] . "</td>" .
				'<td><span title="'. $row['lastupdated']. '"><script>document.write(moment.unix("'. $row['lastupdated']. '").calendar());</script></span></td>' .
				'<td><span title="'. $row['lastupdated']. '" data-livestamp="'. $row['lastupdated']. '"></span></td>' .
				"<td>" . $row["min24"] . "</td>" .
				"<td>" . $row["max24"] . "</td>" .
				"<td>" . $row["delta24"] . "</td>" .
				"</tr>";
			}
		echo "</tbody>";
	echo "</table>";
	echo "</div>";
} else {
    echo "0 results";
}
?>
