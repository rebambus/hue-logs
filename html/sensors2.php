<?php
require_once('mysqli_connect.php');

if (isset($_GET['id'])) {
        $sql = "
        SELECT sensor.sensor_id,
        	sensor.description,
        	log.lastupdated_local,
        	log.type,
        	CASE WHEN log.type = 'lightlevel' THEN CONCAT(ROUND(100*CAST(log.value AS INT)/30000,0),' %')
        		WHEN log.type = 'temperature' THEN CONCAT(ROUND(log.value,1), ' °F')
        		WHEN log.type = 'motion' AND value = '1' THEN CONCAT('Activity for ',(SELECT MIN(next.lastupdated_local) FROM hue_sensor_data_local next WHERE next.sensor_id = log.sensor_id AND next.lastupdated > log.lastupdated))
        		WHEN log.type = 'motion' AND value = '0' THEN CONCAT('No Activity for ',(SELECT MIN(next.lastupdated_local) FROM hue_sensor_data_local next WHERE next.sensor_id = log.sensor_id AND next.lastupdated > log.lastupdated))
        		ELSE log.value
        	END value
        FROM hue_sensor_data_local log
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
        <p>View data for the last
        	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"1")))?>">hour</a>,
        	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"6")))?>">6 hours</a>,
        	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"24")))?>">day</a>,
        	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"168")))?>">week</a>, or
        	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"720")))?>">month</a>.
        </p>

        <!--<div id="chart_div" class="img-fluid"></div> <!--style="width: 100%; height: 400px;"-->-->
    		<div id="chart-container">
    			<canvas id="mycanvas"></canvas>
    		</div>

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
                            echo '<td><span title="'. $row['lastupdated_local']. '">' . $row['lastupdated_local']. '</span></td>';
                        	echo '<td><span title="'. $row['lastupdated_local']. '" data-livestamp="'. $row['lastupdated_local']. '"></span></td>';
                            // echo '<td><span title="'. $row['lastupdated']. '"><script>document.write(moment.utc("'. $row['lastupdated']. '").fromNow());</script></span></td>';
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

    $sql = "
    SELECT sensor.sensor_id,
    	sensor.description,
    	log.lastupdated,
    	log.type,
    	CASE WHEN log.type = 'lightlevel' THEN CONCAT(ROUND(100*CAST(log.value AS UNSIGNED)/30000,0),' %')
    		WHEN log.type = 'temperature' THEN CONCAT(ROUND(value,1), ' °F')
    		WHEN log.type = 'motion' AND log.value = '1' THEN 'Activity'
    		WHEN log.type = 'motion' AND log.value = '0' THEN 'No activity'
    		ELSE log.value
    	END value
    FROM (SELECT sensor_id,
              MAX(lastupdated) lastupdated
          FROM hue_sensor_data
          GROUP BY sensor_id) last_update
    JOIN hue_sensor_data log ON log.sensor_id = last_update.sensor_id AND log.lastupdated = last_update.lastupdated
    JOIN hue_sensors sensor ON sensor.sensor_id = log.sensor_id
    WHERE log.lastupdated IS NOT NULL
    ORDER BY CASE log.type WHEN 'temperature' THEN 1 WHEN 'lightlevel' THEN 2 WHEN 'motion' THEN 3 ELSE 4 END, log.lastupdated DESC;
    ";

    $result = $conn->query($sql);
    ?>

<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Sensor</th>
                <th>Type</th>
                <th>Last Update</th>
                <th>Last Value</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $result->fetch_assoc()) {
            	echo '<tr>';
            	echo '<td><a href="index.php?' . http_build_query(array_merge($_GET, array("id"=>$row['sensor_id']))) . '">'. $row['description'] . '</a></td>';
            	echo '<td>'. $row['type']. '</td>';
            	echo '<td><span title="'. $row['lastupdated_local']. '" data-livestamp="'. $row['lastupdated_local']. '"></span></td>';
            	echo '<td>'. $row['value']. '</td>';
            	echo '</tr>';
            }
            ?>
        </tbody>
    </table>
</div>

<script
  src="https://code.jquery.com/jquery-3.3.1.js"
  integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60="
  crossorigin="anonymous"></script>
<script type="text/javascript" src="js/Chart.min.js"></script>
<script type="text/javascript" src="app.js"></script>

<script>
$(document).ready(function(){
	$.ajax({
		url: "sensor_chart_data.php?id=1",
		method: "GET",
		success: function(data) {
			console.log(data);
			var time = [];
			var value = [];

			for(var i in data) {
				time.push(Date(data[i].lastupdate_local));
				value.push(data[i].value);
			}

			var chartdata = {
				labels: time,
				datasets : [
					{
						label: 'Sensor Value',
						backgroundColor: 'rgba(200, 200, 200, 0.75)',
						borderColor: 'rgba(200, 200, 200, 0.75)',
						hoverBackgroundColor: 'rgba(200, 200, 200, 1)',
						hoverBorderColor: 'rgba(200, 200, 200, 1)',
						data: value
					}
				]
			};

			var ctx = $("#mycanvas");

			var lineGraph = new Chart(ctx, {
				type: 'line',
				data: chartdata
			});
		},
		error: function(data) {
			console.log(data);
		}
	});
});
</script>
