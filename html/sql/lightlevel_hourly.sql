-- VIEW: lightlevel_hourly

SELECT
    CAST(DATE_FORMAT(TIMESTAMPADD(MINUTE,30,lastupdated),'%Y-%m-%d_%H') AS datetime) time_stamp,
    AVG(CASE description WHEN 'Basement' THEN value END) AS basement,
    AVG(CASE description WHEN 'Upstairs Hallway' THEN value END) AS upstairs_hallway,
    AVG(CASE description WHEN 'Bathroom' THEN value END) AS bathroom,
    AVG(CASE description WHEN 'Front Porch' THEN value END) AS front_porch,
    AVG(CASE description WHEN 'Front Hallway' THEN value END) AS front_hallway,
    AVG(CASE description WHEN 'Back Porch' THEN value END) AS back_porch,
    MIN(lastupdated) lastupdated_min,
    MAX(lastupdated) lastupdated_max
FROM hue_sensor_data
JOIN hue_sensors ON hue_sensors.sensor_id = hue_sensor_data.sensor_id
WHERE type = 'lightlevel'
GROUP BY CAST(DATE_FORMAT(TIMESTAMPADD(MINUTE,30,lastupdated),'%Y-%m-%d_%H') AS datetime);
