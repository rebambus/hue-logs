<html>
  <head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['treemap']});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Light', 'Parent', 'Time', 'Brightness'],
          ['All',null,0,0],
          <?php

require_once('mysqli_connect.php');

/*$sql = "SELECT CONCAT('[''', description, ' (', hue_lights.light_id, ')', ''',''All'',' ,
    CASE WHEN brightness = 0 THEN 1 WHEN brightness IS NULL THEN 1 ELSE brightness*10 END, ',' ,
    brightness,
    '],') chart_row
FROM (SELECT light_id, MAX(start_time) start_time
      FROM hue_light_history
      GROUP BY light_id
      ) most_recent_record
JOIN hue_light_history
     ON hue_light_history.light_id = most_recent_record.light_id
     AND hue_light_history.start_time = most_recent_record.start_time
JOIN hue_lights ON hue_lights.light_id = hue_light_history.light_id;
-- WHERE hue_light_history.state = 1
";*/

$sql = "
SELECT CONCAT('[''', description, ''',''All'',0,0],') chart_row
FROM lights
GROUP BY description
UNION ALL
SELECT CONCAT('[''', description, ' (', brightness, ')'',''', description, ''',' ,
    ROUND(SUM(TIMESTAMPDIFF(SECOND,start_time,end_time))/60./60.,2), ',' ,
    brightness,
    '],') chart_row
FROM light_history log
JOIN lights ON lights.id = log.light_id
WHERE start_time > TIMESTAMPADD(DAY,-7,UTC_TIMESTAMP())
  AND state=1
GROUP BY description, brightness;
";

// $json_array = array();
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // $json_array[] = $row;
        echo $row["chart_row"];
    }
    // echo json_encode($json_array);
} else {
}
?>
        ]);

        tree = new google.visualization.TreeMap(document.getElementById('chart_div'));

        tree.draw(data, {
          minColor: 'Maroon',
          midColor: 'Orange',
          maxColor: 'Wheat',
          maxPostDepth: 1,
          useWeightedAverageForAggregation: true,
          // headerHeight: 15,
          // fontColor: 'black',
          showScale: true
        });

      }
    </script>
  </head>
  <body>
    <div id="chart_div" style="width: 100%; height: 600px;"></div>
  </body>
</html>
