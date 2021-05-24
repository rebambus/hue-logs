<?php
require_once('mysqli_connect.php');

$sql = "CALL `temperature_table`();";

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
				echo "<th></th>";
				echo "<th>24 hr max</th>";
				echo "<th></th>";
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
				echo "<td>" . $row["min_diff"] . "</td>";
				echo '<td><span title="'. $row['min_time']. '"><script>document.write(moment.unix("'. $row['min_time']. '").calendar());</script></span></td>';
				echo '<td><span title="'. $row['min_time']. '" data-livestamp="'. $row['min_time']. '"></span></td>';
				echo "<td>" . $row["max_temp"] . "</td>";
				echo "<td>+" . $row["max_diff"] . "</td>";
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
