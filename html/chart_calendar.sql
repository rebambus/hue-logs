SELECT
  UNIX_TIMESTAMP(CAST(CONVERT_TZ(lastupdated,'+00:00',@@global.time_zone) AS DATE)) calendar_date,
  ROUND(MAX(CAST(value AS DECIMAL(6,2))),2) max_outside,
  ROUND(MIN(CAST(value AS DECIMAL(6,2))),2) min_outside
FROM hue_sensor_data
JOIN hue_sensors ON hue_sensors.sensor_id = hue_sensor_data.sensor_id
WHERE type = 'temperature'
  AND description IN ('Back Porch')
GROUP BY UNIX_TIMESTAMP(CAST(CONVERT_TZ(lastupdated,'+00:00',@@global.time_zone) AS DATE))
ORDER BY UNIX_TIMESTAMP(CAST(CONVERT_TZ(lastupdated,'+00:00',@@global.time_zone) AS DATE));