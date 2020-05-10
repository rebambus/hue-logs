<?php
require_once('mysqli_connect.php');

$sql = "SELECT
    sensor.sensor_id,
    sensor.description sensor,
    ROUND(recent.value,1) temperature,
    recent.lastupdated_local lastupdated_local
FROM (SELECT sensor_id, MAX(lastupdated) max_lastupdated FROM hue_sensor_data WHERE type = 'temperature' GROUP BY sensor_id) sensors
LEFT JOIN hue_sensors sensor ON sensor.sensor_id = sensors.sensor_id
LEFT JOIN hue_sensor_data_local recent ON recent.sensor_id = sensors.sensor_id AND recent.lastupdated = sensors.max_lastupdated
ORDER BY CASE description
  WHEN 'Back Porch'       THEN 1
  WHEN 'Front Porch'      THEN 2
  WHEN 'Bathroom'         THEN 3
  WHEN 'Upstairs Hallway' THEN 4
  WHEN 'Front Hallway'    THEN 5
  WHEN 'Basement'         THEN 6
  END;
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo '<div class="table-responsive">';
    echo '<table class="table table-striped table-sm">';
        echo "<thead>";
            echo "<tr><th>Sensor</th><th>Temp</th><th>Time Ago</th></tr>";
        echo "</thead>";
        echo "<tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>" .
                "<td>" . '<a href="index.php?' . http_build_query(array_merge($_GET, array("page"=>"sensors","id"=>$row['sensor_id']))) . '">'. $row['description'] .
                    $row["sensor"] .  '</a>' . "</td>" .
                "<td>" . $row["temperature"] . "</td>" .
                '<td><span title="'. $row['lastupdated_local']. '" data-livestamp="'. $row['lastupdated_local']. '"></span></td>' .
                "</tr>";
            }
        echo "</tbody>";
    echo "</table>";
    echo "</div>";
} else {
    echo "0 results";
}
?>
