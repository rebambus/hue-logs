-- Adminer 4.8.0 MySQL 8.0.25 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DELIMITER ;;

DROP PROCEDURE IF EXISTS `hue_log_sensor_data`;;
CREATE PROCEDURE `hue_log_sensor_data`(IN `uniqueid` varchar(100), IN `description` varchar(100), IN `lastupdated` datetime, IN `type` varchar(100), IN `value` float)
BEGIN
  SET @uniqueid = `uniqueid`;
  SET @description = `description`;
  SET @lastupdated = `lastupdated`;
  SET @type = `type`;
  SET @value = `value`;

  SET @sensor_id = NULL;
  SELECT @sensor_id := sensor_id FROM hue_sensors WHERE hue_sensors.uniqueid = @uniqueid;

  UPDATE hue_sensors
  SET description = @description
  WHERE sensor_id = @sensor_id;

  IF @sensor_id IS NULL THEN
    INSERT INTO hue_sensors (description, uniqueid)
    VALUES (@description, @uniqueid);

    SELECT @sensor_id := sensor_id FROM hue_sensors WHERE hue_sensors.uniqueid = @uniqueid;
  END IF;

  INSERT IGNORE INTO hue_sensor_data (sensor_id, lastupdated, type, value)
  VALUES (@sensor_id, @lastupdated, @type, @value);
END;;

DROP PROCEDURE IF EXISTS `hue_record_light_history`;;
CREATE PROCEDURE `hue_record_light_history`(IN `uniqueid` varchar(100), IN `description` varchar(100), IN `state` bit, IN `brightness` int, IN `reachable` bit)
BEGIN

SELECT @light_id := id
FROM lights
WHERE lights.uniqueid = uniqueid;

IF @light_id IS NULL THEN
	INSERT INTO lights (description, uniqueid)
	VALUES (description, uniqueid);

	SELECT @light_id := lights.id
	FROM lights lights
	WHERE lights.uniqueid = uniqueid;
END IF;

UPDATE lights
SET description = description
WHERE id = @light_id;

-- new
SELECT	@current_id := lh.id,
	@current_state := lh.state,
	@current_brightness := lh.brightness,
	@current_reachable := lh.reachable,
	@current_end_time := lh.end_time
FROM light_history lh
WHERE lh.light_id = @light_id
ORDER BY lh.id DESC
LIMIT 1;

IF	@current_state = state
	AND @current_brightness = brightness
	AND @current_reachable = reachable
	AND @current_end_time >= DATE_ADD(UTC_TIMESTAMP(), INTERVAL -5 MINUTE)
THEN
	UPDATE light_history
	SET end_time = UTC_TIMESTAMP()
	WHERE id = @current_id;
ELSE
	INSERT INTO light_history (light_id, start_time, end_time, state, brightness, reachable)
	VALUES (@light_id, UTC_TIMESTAMP(), UTC_TIMESTAMP(), state, brightness, reachable);
END IF;

END;;

DROP PROCEDURE IF EXISTS `min_max_temp_by_day`;;
CREATE PROCEDURE `min_max_temp_by_day`()
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
         sensor;;

DROP PROCEDURE IF EXISTS `temperature_table`;;
CREATE PROCEDURE `temperature_table`()
BEGIN
CREATE TEMPORARY TABLE temp_last_day
SELECT   sensor_id, value, lastupdated
FROM     hue_sensor_data AS h2
WHERE    type = 'temperature'
         AND lastupdated >= timestampadd(DAY, -1, UTC_TIMESTAMP());

CREATE TEMPORARY TABLE newest_row
SELECT   MAX(id) AS max_id
FROM     hue_sensor_data
GROUP BY sensor_id;

CREATE TEMPORARY TABLE max_temp
WITH max_temp AS (
SELECT sensor_id, value, lastupdated,
	ROW_NUMBER() OVER (PARTITION BY sensor_id ORDER BY value DESC, lastupdated DESC) AS row_num
FROM temp_last_day)
SELECT sensor_id, value, lastupdated
FROM max_temp
WHERE row_num = 1;

CREATE TEMPORARY TABLE min_temp
WITH min_temp AS (
SELECT sensor_id, value, lastupdated,
	ROW_NUMBER() OVER (PARTITION BY sensor_id ORDER BY value, lastupdated DESC) AS row_num
FROM temp_last_day)
SELECT sensor_id, value, lastupdated
FROM min_temp
WHERE row_num = 1;

SELECT   sensor.sensor_id,
         sensor.description,
         ROUND(sensor_data.value, 0) AS temperature,
         UNIX_TIMESTAMP(sensor_data.lastupdated) AS time,
         ROUND(min_temp.value, 0) AS min_temp,
         ROUND(min_temp.value - sensor_data.value, 0) AS min_diff,
         UNIX_TIMESTAMP(min_temp.lastupdated) AS min_time,
         ROUND(max_temp.value, 0) AS max_temp,
         ROUND(max_temp.value - sensor_data.value, 0) AS max_diff,
         UNIX_TIMESTAMP(max_temp.lastupdated) AS max_time
FROM     newest_row
         JOIN hue_sensor_data AS sensor_data
             ON sensor_data.id = newest_row.max_id
         JOIN hue_sensors AS sensor
             ON sensor.sensor_id = sensor_data.sensor_id
         LEFT JOIN min_temp on min_temp.sensor_id = sensor.sensor_id
         LEFT JOIN max_temp on max_temp.sensor_id = sensor.sensor_id
WHERE    sensor_data.type = 'temperature'
ORDER BY CASE WHEN DESCRIPTION LIKE '%porch%' THEN 1
             ELSE 2
         END,
         sensor.DESCRIPTION;
END;;

DELIMITER ;

DROP TABLE IF EXISTS `hue_sensor_data`;
CREATE TABLE `hue_sensor_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sensor_id` int NOT NULL,
  `lastupdated` datetime NOT NULL,
  `type` varchar(100) NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sensor_id_lastupdated` (`sensor_id`,`lastupdated`),
  KEY `lastupdated` (`lastupdated`),
  KEY `sensor_id` (`sensor_id`),
  CONSTRAINT `hue_sensor_data_ibfk_1` FOREIGN KEY (`sensor_id`) REFERENCES `hue_sensors` (`sensor_id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `hue_sensors`;
CREATE TABLE `hue_sensors` (
  `sensor_id` int NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  `uniqueid` varchar(100) NOT NULL,
  PRIMARY KEY (`sensor_id`),
  KEY `uniqueid` (`uniqueid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `light_history`;
CREATE TABLE `light_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `light_id` int NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `state` bit(1) NOT NULL,
  `brightness` int NOT NULL,
  `reachable` bit(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `light_id` (`light_id`),
  KEY `start_time` (`start_time`),
  KEY `end_time` (`end_time`),
  CONSTRAINT `light_history_ibfk_1` FOREIGN KEY (`light_id`) REFERENCES `lights` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


DROP TABLE IF EXISTS `lights`;
CREATE TABLE `lights` (
  `id` int NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  `uniqueid` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqueid` (`uniqueid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


-- 2021-05-24 17:43:44