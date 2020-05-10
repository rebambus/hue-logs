<?php

function temp_chart_data($value)
{
    require_once('mysqli_connect.php');

    $sql = file_get_contents("chart_calendar.sql");

    if (isset($_GET['hours_back'])) {
        $sql = str_replace("HOUR,-24", "HOUR,-".$_GET['hours_back'], $sql);
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
            echo '<th>Date</th>';
            echo '<th>Min Outside</th>';
            echo '<th>Max Outside</th>';
            echo '</tr>';
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>'. $row['calendar_date']. '</td>';
                echo '<td>'. $row['min_outside']. '</td>';
                echo '<td>'. $row['max_outside']. '</td>';
                echo '</tr>';
            }
            echo '</table>';
            return;
        }
    }

    $table = array();
    $table['cols'] = array(
    array('id' => 'Date', 'type' => 'date'),
    array('id' => 'Temp', 'type' => 'number')
  );

    $rows = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $temp = array();

            $temp[] = array('v' => $row['calendar_date']);
            if ($value = 'max') {
                $temp[] = array('v' => $row['max_outside']);
            } elseif ($value = 'min') {
                $temp[] = array('v' => $row['min_outside']);
            } else {
                $temp[] = array('v' => 0);
            }

            $rows[] = array('c' => $temp);
        }

        $table['rows'] = $rows;
        $jsonTable = json_encode($table);

        echo $jsonTable;
    } else {
    }
}
