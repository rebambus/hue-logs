<?php
//setting header to json
header('Content-Type: application/json');

//database
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'tom');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'tomdb');

//get connection
$mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

if(!$mysqli){
	die("Connection failed: " . $mysqli->error);
}

//query to get data from the table
$query = sprintf("
SELECT lastupdated_local,
	IFNULL(CASE WHEN type = 'lightlevel' THEN 100*CAST(value AS INT)/30000 ELSE value END,'null') value
FROM hue_sensor_data_local log
WHERE log.sensor_id = ". $_GET['id']. "
	AND log.lastupdated > TIMESTAMPADD(HOUR,-@hours_back,UTC_TIMESTAMP())
ORDER BY log.lastupdated DESC
LIMIT 10000;
");

if (!isset($_GET['hours_back'])) {
	$hours_back = 24;
} else {
	$hours_back = $_GET['hours_back'];
}
$query = str_replace("@hours_back", $hours_back, $query);

//execute query
$result = $mysqli->query($query);

//loop through the returned data
$data = array();
foreach ($result as $row) {
	$data[] = $row;
}

//free memory associated with result
$result->close();

//close connection
$mysqli->close();

//now print the data
print json_encode($data);
