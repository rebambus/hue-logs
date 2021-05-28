<?php

require_once('mysqli_connect.php');

$sql_chart_temp = file_get_contents("chart_temperature.sql");

if (!isset($_GET['hours_back'])) {
	$hours_back = 24;
} else {
	$hours_back = $_GET['hours_back'];
}
$sql_chart_temp = str_replace("@hours_back", $hours_back, $sql_chart_temp);

// if ?output=sql, return SQL query
if (isset($_GET['output'])) {
    if ($_GET['output'] == 'sql') {
        echo '<pre>';
        echo $sql_chart_temp;
        echo '</pre>';
        return;
    }
}

// the SQL call!
$result_chart_temp = $conn->query($sql_chart_temp);

if (!$result_chart_temp) {
    trigger_error('Invalid query: ' . $conn->error);
}

if (isset($_GET['output'])) {
    if ($_GET['output'] == 'table') {
        echo '<table>';
        echo '<tr>';
        echo '<th>Time</th>';
        echo '<th>Back Porch</th>';
        echo '<th>Front Porch</th>';
        echo '<th>Front Porch old</th>';
        echo '<th>Basement</th>';
        echo '<th>Bathroom</th>';
        echo '<th>Front Hallway</th>';
        echo '<th>Living Room</th>';
        echo '<th>Upstairs Hallway</th>';
        echo '</tr>';
        while ($row = $result_chart_temp->fetch_assoc()) {
            echo '<tr>';
            echo '<td>'. $row['lastupdated']. '</td>';
            echo '<td>'. $row['back_porch']. '</td>';
            echo '<td>'. $row['front_porch']. '</td>';
            echo '<td>'. $row['front_porch_old']. '</td>';
            echo '<td>'. $row['basement']. '</td>';
            echo '<td>'. $row['bathroom']. '</td>';
            echo '<td>'. $row['front_hallway']. '</td>';
            echo '<td>'. $row['living_room']. '</td>';
            echo '<td>'. $row['upstairs_hallway']. '</td>';
            echo '</tr>';
        }
        echo '</table>';
        return;
    }
}

$table = array();
$table['cols'] = array(
  array('id' => 'Last_Updated',	'label' => 'Last Updated',	'type' => 'datetime',	'role' => 'domain'),
  array('id' => 'Back_Porch',	'label' => 'Back Porch',	'type' => 'number'),
  array('id' => 'Front_Porch',	'label' => 'Front Porch',	'type' => 'number'),
  array('id' => 'Front_Porch_old',	'label' => 'Front Porch old',	'type' => 'number'),
  array('id' => 'Basement',	'label' => 'Basement',	'type' => 'number'),
  array('id' => 'Bathroom',	'label' => 'Bathroom',	'type' => 'number'),
  array('id' => 'Front_Hallway',	'label' => 'Front Hallway',	'type' => 'number'),
  array('id' => 'Living_Room',	'label' => 'Living Room',	'type' => 'number'),
  array('id' => 'Upstairs_Hallway',	'label' => 'Upstairs Hallway',	'type' => 'number')
);

$rows = array();
if ($result_chart_temp->num_rows > 0) {
    while ($row = $result_chart_temp->fetch_assoc()) {
        $temp = array();

        $temp[] = array('v' => 'Date(' . 1000*$row['lastupdated'] .')');
        $temp[] = array('v' => $row['back_porch']);;
        $temp[] = array('v' => $row['front_porch']);
        $temp[] = array('v' => $row['front_porch_old']);
        $temp[] = array('v' => $row['basement']);
        $temp[] = array('v' => $row['bathroom']);
        $temp[] = array('v' => $row['front_hallway']);
        $temp[] = array('v' => $row['living_room']);
        $temp[] = array('v' => $row['upstairs_hallway']);

        $rows[] = array('c' => $temp);
        ;
    }

    $table['rows'] = $rows;
    $jsonTable = json_encode($table);

    // header('Content-Type: application/json;charset=utf-8'); // include if a function instead of an include
    echo $jsonTable;
} else {
}
