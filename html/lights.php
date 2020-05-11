<script type="text/javascript" src="http://momentjs.com/downloads/moment.js"></script>
<?php
require_once('mysqli_connect.php');

if (isset($_GET['id'])) {
// history for one light
$sql = "
SELECT  lh.light_id,
	lights.description,
	UNIX_TIMESTAMP(lh.start_time) start_time,
	UNIX_TIMESTAMP(lh.end_time) end_time,
	CASE WHEN state = 1 THEN 'On' WHEN state = 0 THEN 'Off' WHEN state IS NULL THEN 'Unavailable' ELSE 'Unknown' END state,
	CONCAT(ROUND(100.*brightness/254.,0),' %') brightness
FROM light_history lh
JOIN lights ON lights.id = lh.light_id
WHERE lh.light_id = " . $_GET['id'] . "
ORDER BY lh.id DESC
LIMIT 10;
";

    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $result->data_seek(0); // Just tickle the first row, then rewind.
    echo '<h1 class="h2">' . $row['description'] . '</h1>';

?>
<p>View data for the last
	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"1")))?>">hour</a>,
	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"6")))?>">6 hours</a>,
	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"24")))?>">day</a>,
	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"168")))?>">week</a>, or
	<a href="index.php?<?php echo http_build_query(array_merge($_GET, array("hours_back"=>"720")))?>">month</a>.
</p>

<div id="chart_div" style="width: 100%; height: 400px;"></div>

<div class="table-responsive">
    <table class="table table-striped table-sm">
        <tr>
            <th>Time</th>
            <th>Time Ago</th>
            <th>Duration</th>
            <th>State</th>
            <th>Brightness</th>
        </tr>
        <?php
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td><span title="<script>document.write(moment.unix("'. $row['start_time']. '").format());</script>"><script>document.write(moment.unix("'. $row['start_time']. '").local().calendar());</script></span></td>';
                echo '<td><script>document.write(moment.unix("'. $row['start_time']. '").fromNow());</script></td>';
                echo '<td><script>document.write(moment.unix("'. $row['end_time']. '").from(moment.unix("'. $row['start_time']. '"),true));</script></td>';
                echo '<td>'. $row['state']. '</td>';
                echo '<td>'. $row['brightness']. '</td>';
                echo '</tr>';
            }
            ?>
    </table>
</div>

    <?php
} // end of if (isset($_GET['id']))
else {
    echo '<h1 class="h2">Lights</h1>';
}

// list of all lights
$sql = "
SELECT  log.light_id,
        lights.description,
        UNIX_TIMESTAMP(log.start_time) start_time,
        UNIX_TIMESTAMP(log.end_time) end_time,
        CASE WHEN state IS NULL THEN ''
          WHEN state = 0 THEN 'Off'
          WHEN state = 1 AND brightness = 0 THEN 'On'
          WHEN state = 1 THEN CONCAT(ROUND(100.*brightness/254.,0),' %') ELSE 'Unknown' END state
FROM light_history log
JOIN lights lights ON lights.id = log.light_id
WHERE log.id IN (SELECT MAX(id) FROM light_history GROUP BY light_id)
ORDER BY COALESCE(state,-1) DESC, log.start_time DESC, description;
";

	$result = $conn->query($sql);
?>

<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Light</th>
                <th>State</th>
                <th>Last Change</th>
                <th>Time Ago</th>
            </tr>
        </thead>
        <tbody>
            <?php
        	while ($row = $result->fetch_assoc()) {
		echo '<tr>';
		echo '<td><a href="index.php?page=lights&id=' . $row['light_id'] . '">'. $row['description'] . '</a></td>';
		echo '<td>'. $row['state']. '</td>';
		echo '<td><span title="<script>document.write(moment.unix("'. $row['start_time']. '").format());</script>"><script>document.write(moment.unix("'. $row['start_time']. '").calendar());</script></span></td>';
		echo '<td><span title="<script>document.write(moment.unix("'. $row['start_time']. '").format());</script>" data-livestamp="'. $row['start_time']. '"></span></td>';
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
	'new Date(', 1000*UNIX_TIMESTAMP(end_time), '),',
	IFNULL(CASE WHEN state = 1 THEN brightness ELSE 0 END,'null'), ',',
	'],[',
	'new Date(', 1000*UNIX_TIMESTAMP(CASE WHEN TIMESTAMPADD(HOUR,-@hours_back,UTC_TIMESTAMP()) > start_time THEN TIMESTAMPADD(HOUR,-@hours_back,UTC_TIMESTAMP()) ELSE start_time END), '),',
	IFNULL(CASE WHEN state = 1 THEN brightness ELSE 0 END,'null'), ',',
	'],') chart_row -- list with 2 elements (2 records, start and end)
FROM light_history lh
WHERE light_id = ". $_GET['id']. "
	AND end_time > TIMESTAMPADD(HOUR,-@hours_back,UTC_TIMESTAMP())
ORDER BY lh.id DESC
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

		var options = {
			title: 'Value',
			//curveType: 'function',
			hAxis: {
				title: 'Date and Time'
			},
			vAxis: {
				title: 'Value'
			}
		};

		var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));

		chart.draw(data, options);
	}
</script>
