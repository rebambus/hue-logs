SELECT
		UNIX_TIMESTAMP(lastupdated) AS lastupdated,
		ROUND(basement,2) basement,
		ROUND(upstairs_hallway,2) upstairs_hallway,
		ROUND(bathroom,2) bathroom,
		ROUND(front_porch,2) front_porch,
		ROUND(front_porch_old,2) front_porch_old,
		ROUND(front_hallway,2) front_hallway,
		ROUND(back_porch,2) back_porch,
		ROUND(outdoor_sensor,2) outdoor_sensor
FROM (SELECT
	 			FROM_UNIXTIME(MAX(UNIX_TIMESTAMP(lastupdated))) lastupdated,
				AVG(CASE description WHEN 'Basement'         THEN value END) AS basement,
				AVG(CASE description WHEN 'Upstairs Hallway' THEN value END) AS upstairs_hallway,
				AVG(CASE description WHEN 'Bathroom'         THEN value END) AS bathroom,
				AVG(CASE description WHEN 'Front Porch'      THEN value END) AS front_porch,
				AVG(CASE description WHEN 'Front Porch old'  THEN value END) AS front_porch_old,
				AVG(CASE description WHEN 'Front Hallway'    THEN value END) AS front_hallway,
				AVG(CASE description WHEN 'Back Porch'       THEN value END) AS back_porch,
				AVG(CASE description WHEN 'Outdoor Sensor'   THEN value END) AS outdoor_sensor
		FROM hue_sensor_data
		JOIN hue_sensors ON hue_sensors.sensor_id = hue_sensor_data.sensor_id
		WHERE type = 'lightlevel'
			AND lastupdated > TIMESTAMPADD(HOUR,-24,UTC_TIMESTAMP())
		GROUP BY FLOOR(UNIX_TIMESTAMP(lastupdated)/60/15)
	) chart_data
ORDER BY chart_data.lastupdated;
