SELECT
	description row_label,
	CASE value WHEN '1' THEN 'Activity' ELSE 'No Activity' END bar_label,
	UNIX_TIMESTAMP(lastupdated) AS start_time,
	UNIX_TIMESTAMP(lastupdated) AS end_time
FROM hue_sensor_data
JOIN hue_sensors ON hue_sensors.sensor_id = hue_sensor_data.sensor_id
WHERE type = 'motion'
	AND lastupdated > TIMESTAMPADD(HOUR,-24,UTC_TIMESTAMP())
ORDER BY description;
