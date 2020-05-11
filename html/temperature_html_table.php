<?php
require_once('mysqli_connect.php');

$sql = "
SELECT
    sensor.sensor_id,
    sensor.description sensor,
    ROUND(log.value,1) temperature,
    UNIX_TIMESTAMP(log.lastupdated) lastupdated
FROM hue_sensor_data log
JOIN hue_sensors sensor ON sensor.sensor_id = log.sensor_id
WHERE id IN (SELECT MAX(id) FROM hue_sensor_data GROUP BY sensor_id)
	AND type = 'temperature'
ORDER BY CASE WHEN description LIKE '%porch%' THEN 1 ELSE 2 END;
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo '<div class="table-responsive">';
    echo '<table class="table table-striped table-sm">';
        echo "<thead>";
            echo "<tr><th>Sensor</th><th>Temp</th><th>Time</th><th>Time Ago</th></tr>";
        echo "</thead>";
        echo "<tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>" .
                "<td>" . '<a href="index.php?' . http_build_query(array_merge($_GET, array("page"=>"sensors","id"=>$row['sensor_id']))) . '">'. $row['description'] .
                    $row["sensor"] .  '</a>' . "</td>" .
                "<td>" . $row["temperature"] . "</td>" .
				'<td><span title="'. $row['lastupdated']. '"><script>document.write(moment.unix("'. $row['lastupdated']. '").calendar());</script></span></td>' .
                '<td><span title="'. $row['lastupdated']. '" data-livestamp="'. $row['lastupdated']. '"></span></td>' .
                "</tr>";
            }
        echo "</tbody>";
    echo "</table>";
    echo "</div>";
} else {
    echo "0 results";
}
?>
