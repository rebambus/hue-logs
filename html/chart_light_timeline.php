<?php

require_once('mysqli_connect.php');

$sql = file_get_contents("chart_light_timeline.sql");

if (isset($_GET['hours_back'])) {
    $sql = str_replace("HOUR, -24", "HOUR, -".$_GET['hours_back'], $sql);
}

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
        echo '<th>Light</th>';
        echo '<th>State</th>';
        echo '<th>Start</th>';
        echo '<th>End</th>';
        echo '</tr>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>'. $row['row_label']. '</td>';
            echo '<td>'. $row['bar_label']. '</td>';
            echo '<td>'. $row['start_time']. '</td>';
            echo '<td>'. $row['end_time']. '</td>';
            echo '</tr>';
        }
        echo '</table>';
        return;
    }
}

$table = array();
$table['cols'] = array(
  array('id' => 'Light', 'type' => 'string'),
  array('id' => 'State', 'type' => 'string'),
  array('id' => 'Start', 'type' => 'date'),
  array('id' => 'End', 'type' => 'date')
);

$rows = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $temp = array();

        $temp[] = array('v' => $row['row_label']);
        $temp[] = array('v' => $row['bar_label']);
        $temp[] = array('v' => 'Date(' . 1000 * $row['start_time'] . ')');
        $temp[] = array('v' => 'Date(' . 1000 * $row['end_time'] . ')');

        $rows[] = array('c' => $temp);
    }

    $table['rows'] = $rows;
    $jsonTable = json_encode($table);

    echo $jsonTable;
} else {
}
