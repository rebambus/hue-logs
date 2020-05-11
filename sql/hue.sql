-- Adminer 4.7.6 MySQL dump

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

		SELECT @light_id := id
		FROM lights
		WHERE lights.uniqueid = uniqueid;
	END IF;
	
	SELECT @id := MAX(id)
	FROM light_history lh
	WHERE lh.id IN (SELECT MAX(id) FROM light_history GROUP BY light_id)
                AND lh.light_id = @light_id
		AND lh.state = state
		AND lh.brightness = brightness
		AND lh.reachable = reachable
		AND lh.end_time >= DATE_ADD(UTC_TIMESTAMP(), INTERVAL -5 MINUTE);

	IF @id IS NOT NULL THEN
		UPDATE light_history
		SET end_time = UTC_TIMESTAMP()
		WHERE light_history.id = @id;
	ELSE
		INSERT INTO light_history (light_id, start_time, end_time, state, brightness, reachable)
		VALUES (@light_id, UTC_TIMESTAMP(), UTC_TIMESTAMP(), state, brightness, reachable);
	END IF;
END;;

DELIMITER ;

DROP TABLE IF EXISTS `hue_sensors`;
CREATE TABLE `hue_sensors` (
  `sensor_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  `uniqueid` varchar(100) NOT NULL,
  PRIMARY KEY (`sensor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `hue_sensor_data`;
CREATE TABLE `hue_sensor_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sensor_id` int(11) NOT NULL,
  `lastupdated` datetime NOT NULL,
  `type` varchar(100) NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sensor_id_lastupdated` (`sensor_id`,`lastupdated`),
  KEY `sensor_id` (`sensor_id`),
  CONSTRAINT `hue_sensor_data_ibfk_1` FOREIGN KEY (`sensor_id`) REFERENCES `hue_sensors` (`sensor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `lights`;
CREATE TABLE `lights` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  `uniqueid` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `light_history`;
CREATE TABLE `light_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `light_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `state` bit(1) NOT NULL,
  `brightness` int(11) NOT NULL,
  `reachable` bit(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `light_id` (`light_id`),
  CONSTRAINT `light_history_ibfk_1` FOREIGN KEY (`light_id`) REFERENCES `lights` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2020-05-11 14:26:28
