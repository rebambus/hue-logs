SELECT CONCAT('[new Date(''', CONVERT_TZ(time_stamp, '+00:00', '-04:00'), '''),',
	IFNULL(ROUND(back_porch,2),'null'), ',',
	IFNULL(ROUND(front_porch,2),'null'), ',',
  IFNULL(ROUND(basement,2),'null'), ',',
  IFNULL(ROUND(upstairs_hallway,2),'null'), ',',
  IFNULL(ROUND(bathroom,2),'null'), ',',
  IFNULL(ROUND(front_hallway,2),'null'),
  '],') chart_row -- list with 7 elements
FROM (SELECT
		    CAST(DATE_FORMAT(TIMESTAMPADD(MINUTE,30,lastupdated),'%Y-%m-%d_%H') AS datetime) time_stamp,
		    AVG(CASE description WHEN 'Basement' THEN value END) AS basement,
		    AVG(CASE description WHEN 'Upstairs Hallway' THEN value END) AS upstairs_hallway,
		    AVG(CASE description WHEN 'Bathroom' THEN value END) AS bathroom,
		    AVG(CASE description WHEN 'Front Porch' THEN value END) AS front_porch,
		    AVG(CASE description WHEN 'Front Hallway' THEN value END) AS front_hallway,
		    AVG(CASE description WHEN 'Back Porch' THEN value END) AS back_porch,
		    MIN(CASE description WHEN 'Front Porch' OR 'Back Porch' THEN value END) AS min_outdoor,
		    MAX(CASE description WHEN 'Front Porch' OR 'Back Porch' THEN value END) AS max_outdoor,
				COUNT(*) AS num_records
		    MIN(lastupdated) lastupdated_min,
		    MAX(lastupdated) lastupdated_max
		FROM hue_sensor_data
		JOIN hue_sensors ON hue_sensors.sensor_id = hue_sensor_data.sensor_id
		WHERE type = 'temperature'
		GROUP BY CAST(DATE_FORMAT(TIMESTAMPADD(MINUTE,30,lastupdated),'%Y-%m-%d_%H') AS datetime)) chart_data
WHERE lastupdated_min > TIMESTAMPADD(HOUR,-6,UTC_TIMESTAMP())
ORDER BY time_stamp;
