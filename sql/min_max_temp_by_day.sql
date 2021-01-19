SELECT   s.description AS sensor,
         CAST(sd.lastupdated AS date) AS date,
         MIN(sd.value) AS min_temp,
         MAX(sd.value) AS max_temp
FROM     hue_sensor_data AS sd
         LEFT JOIN hue_sensors AS s
             ON s.sensor_id = sd.sensor_id
WHERE    sd.type = 'temperature'
GROUP BY s.description,
         CAST(sd.lastupdated AS date)
ORDER BY date DESC,
         sensor