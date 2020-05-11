<?php

require_once('mysqli_connect.php');

$sql = file_get_contents("chart_temperature.sql");

if (!isset($_GET['hours_back'])) {
	$hours_back = 24;
} else {
	$hours_back = $_GET['hours_back'];
}
$sql = str_replace("@hours_back", $hours_back, $sql);

// if ?output=sql, return SQL query
if (isset($_GET['output'])) {
    if ($_GET['output'] == 'sql') {
        echo '<pre>';
        echo $sql;
        echo '</pre>';
        return;
    }
}

// the SQL call!
$result = $conn->query($sql);

if (isset($_GET['output'])) {
    if ($_GET['output'] == 'table') {
        echo '<table>';
        echo '<tr>';
        echo '<th>Time</th>';
        echo '<th>Basement</th>';
        echo '<th>Upstairs Hallway</th>';
        echo '<th>Bathroom</th>';
        echo '<th>Front Porch</th>';
        echo '<th>Front Hallway</th>';
        echo '<th>Back Porch</th>';
        echo '</tr>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>'. $row['lastupdated']. '</td>';
            echo '<td>'. $row['basement']. '</td>';
            echo '<td>'. $row['upstairs_hallway']. '</td>';
            echo '<td>'. $row['bathroom']. '</td>';
            echo '<td>'. $row['front_porch']. '</td>';
            echo '<td>'. $row['front_hallway']. '</td>';
            echo '<td>'. $row['back_porch']. '</td>';
            echo '</tr>';
        }
        echo '</table>';
        return;
    }
}

$table = array();
$table['cols'] = array(
  array('id' => 'Last Updated',	'label' => 'Last Updated',	'type' => 'datetime',	'role' => 'domain'),
  array('id' => 'Basement',	'label' => 'Basement',	'type' => 'number'),
  array('id' => 'Upstairs_Hallway',	'label' => 'Upstairs_Hallway',	'type' => 'number'),
  array('id' => 'Bathroom',	'label' => 'Bathroom',	'type' => 'number'),
  array('id' => 'Front_Porch',	'label' => 'Front_Porch',	'type' => 'number'),
  array('id' => 'Front_Hallway',	'label' => 'Front_Hallway',	'type' => 'number'),
  array('id' => 'Back_Porch',	'label' => 'Back_Porch',	'type' => 'number')
);

$rows = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $temp = array();

        $temp[] = array('v' => 'Date(' . 1000*$row['lastupdated'] .')');
        $temp[] = array('v' => $row['basement']);
        $temp[] = array('v' => $row['upstairs_hallway']);
        $temp[] = array('v' => $row['bathroom']);
        $temp[] = array('v' => $row['front_porch']);
        $temp[] = array('v' => $row['front_hallway']);
        $temp[] = array('v' => $row['back_porch']);

        $rows[] = array('c' => $temp);
        ;
    }

    $table['rows'] = $rows;
    $jsonTable = json_encode($table);

    // header('Content-Type: application/json;charset=utf-8'); // include if a function instead of an include
    echo $jsonTable;
} else {
}
