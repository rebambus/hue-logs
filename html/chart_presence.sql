SELECT
	description row_label,
	CASE value WHEN '1' THEN 'Activity' ELSE 'No Activity' END bar_label,
	json_date(CONVERT_TZ(lastupdated,'+00:00',@@global.time_zone)) start_time,
	json_date(CONVERT_TZ(lastupdated,'+00:00',@@global.time_zone)) end_time
FROM hue_sensor_data
JOIN hue_sensors ON hue_sensors.sensor_id = hue_sensor_data.sensor_id
WHERE type = 'presence'
	AND lastupdated > TIMESTAMPADD(HOUR,-24,UTC_TIMESTAMP())
ORDER BY description;
