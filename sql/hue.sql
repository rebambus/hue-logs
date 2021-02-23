-- Adminer 4.7.8 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP DATABASE IF EXISTS `hue`;
CREATE DATABASE `hue` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `hue`;

DELIMITER ;;

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

DELIMITER ;

DROP TABLE IF EXISTS `hue_sensors`;
CREATE TABLE `hue_sensors` (
  `sensor_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  `uniqueid` varchar(100) NOT NULL,
  PRIMARY KEY (`sensor_id`),
  KEY `uniqueid` (`uniqueid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `hue_sensors` (`sensor_id`, `description`, `uniqueid`) VALUES
(2,	'Front Porch old',	'00:17:88:01:02:10:1a:96-02-0406'),
(3,	'Front Porch old',	'00:17:88:01:02:10:1a:96-02-0402'),
(4,	'Upstairs Hallway',	'00:17:88:01:02:03:b2:f2-02-0406'),
(5,	'Upstairs Hallway',	'00:17:88:01:02:03:b2:f2-02-0400'),
(6,	'Front Hallway',	'00:17:88:01:02:10:de:e8-02-0400'),
(7,	'Front Hallway',	'00:17:88:01:02:10:de:e8-02-0402'),
(8,	'Front Hallway',	'00:17:88:01:02:10:de:e8-02-0406'),
(9,	'Bathroom',	'00:17:88:01:02:10:d9:72-02-0406'),
(10,	'Bathroom',	'00:17:88:01:02:10:d9:72-02-0400'),
(11,	'Bathroom',	'00:17:88:01:02:10:d9:72-02-0402'),
(12,	'Front Porch old',	'00:17:88:01:02:10:1a:96-02-0400'),
(13,	'Basement',	'00:17:88:01:02:03:b2:71-02-0402'),
(14,	'Basement',	'00:17:88:01:02:03:b2:71-02-0400'),
(15,	'Basement',	'00:17:88:01:02:03:b2:71-02-0406'),
(16,	'Upstairs Hallway',	'00:17:88:01:02:03:b2:f2-02-0402'),
(17,	'Living Room',	'00:17:88:01:02:03:ee:fe-02-0406'),
(18,	'Living Room',	'00:17:88:01:02:03:ee:fe-02-0402'),
(19,	'Living Room',	'00:17:88:01:02:03:ee:fe-02-0400'),
(20,	'Back Porch',	'00:17:88:01:06:45:44:ec-02-0406'),
(21,	'Back Porch',	'00:17:88:01:06:45:44:ec-02-0400'),
(22,	'Back Porch',	'00:17:88:01:06:45:44:ec-02-0402'),
(23,	'Front Porch',	'00:17:88:01:06:45:ac:29-02-0400'),
(24,	'Front Porch',	'00:17:88:01:06:45:ac:29-02-0406'),
(25,	'Front Porch',	'00:17:88:01:06:45:ac:29-02-0402');

DROP TABLE IF EXISTS `hue_sensor_data`;
CREATE TABLE `hue_sensor_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sensor_id` int(11) NOT NULL,
  `lastupdated` datetime NOT NULL,
  `type` varchar(100) NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sensor_id_lastupdated` (`sensor_id`,`lastupdated`),
  KEY `lastupdated` (`lastupdated`),
  CONSTRAINT `hue_sensor_data_ibfk_1` FOREIGN KEY (`sensor_id`) REFERENCES `hue_sensors` (`sensor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `lights`;
CREATE TABLE `lights` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  `uniqueid` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uniqueid` (`uniqueid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `lights` (`id`, `description`, `uniqueid`) VALUES
(2,	'Chandelier 4',	'00:17:88:01:06:c0:80:d7-0b'),
(3,	'Chandelier 5',	'00:17:88:01:06:c0:7d:02-0b'),
(4,	'Blue Lamp',	'00:17:88:01:02:96:b9:db-0b'),
(5,	'Bathroom 3',	'00:17:88:01:02:95:0a:d2-0b'),
(6,	'Fringe Floor Lamp',	'00:17:88:01:02:96:bb:e9-0b'),
(7,	'Green Lamp',	'00:17:88:01:02:f4:c9:37-0b'),
(10,	'Pinball Room Ceiling 1',	'00:17:88:01:02:f5:29:ea-0b'),
(11,	'Bathroom 1',	'00:17:88:01:02:96:bf:48-0b'),
(12,	'Basement Room',	'00:17:88:01:02:1e:80:61-0b'),
(13,	'Counter Left',	'00:17:88:01:02:ad:cf:bb-0b'),
(14,	'Chandelier 3',	'00:17:88:01:06:c0:7c:64-0b'),
(15,	'Chandelier 6',	'00:17:88:01:06:c0:93:c8-0b'),
(16,	'New Globe',	'00:17:88:01:02:32:48:bb-0b'),
(17,	'Bedroom Floor Lamp',	'00:17:88:01:02:34:7a:9c-0b'),
(18,	'Bookshelf',	'00:17:88:01:02:32:72:8b-0b'),
(19,	'Basement by Washer',	'00:17:88:01:02:28:d5:ab-0b'),
(20,	'Light above the sink',	'00:17:88:01:02:3f:d5:51-0b'),
(21,	'Little Lamp',	'00:17:88:01:02:56:67:61-0b'),
(22,	'Basement Steps',	'00:17:88:01:02:56:67:21-0b'),
(23,	'Basement by Water Heater',	'00:17:88:01:02:24:82:e3-0b'),
(24,	'Basement By Dryer',	'00:17:88:01:02:2a:dd:ed-0b'),
(25,	'Chandelier 1',	'00:17:88:01:06:c0:85:0c-0b'),
(26,	'Chandelier 2',	'00:17:88:01:06:c0:7e:94-0b'),
(27,	'Yellow Lamp',	'00:17:88:01:02:1f:81:27-0b'),
(28,	'Green Bedroom Lamp',	'00:17:88:01:02:1f:55:ad-0b'),
(29,	'Front Porch',	'00:17:88:01:02:1e:95:c3-0b'),
(30,	'IKEA Floor Lamp',	'00:17:88:01:02:1f:78:85-0b'),
(31,	'Teal and Bronze Lamp',	'00:17:88:01:02:f7:e0:8b-0b'),
(32,	'Teal Lamp',	'00:17:88:01:02:83:1c:3a-0b'),
(33,	'Basement by Furnace',	'00:17:88:01:02:f0:2d:4f-0b'),
(34,	'Back Porch',	'00:17:88:01:02:80:43:b2-0b'),
(35,	'Pinball Room Ceiling 2',	'00:17:88:01:02:80:1d:4f-0b'),
(36,	'Bathroom 2',	'00:17:88:01:02:80:2e:df-0b'),
(37,	'Spare Room Ceiling 1',	'00:17:88:01:02:56:93:49-0b'),
(38,	'Counter Right',	'00:17:88:01:02:ad:cf:a7-0b'),
(39,	'Outdoor String Lights',	'00:17:88:01:08:05:51:6f-0b'),
(40,	'Front Hallway',	'00:17:88:01:08:14:23:c7-0b'),
(41,	'Dining Room Light 3',	'00:17:88:01:06:c2:bd:9b-0b'),
(42,	'Dining Room Light 2',	'00:17:88:01:06:c2:d6:b6-0b'),
(43,	'Dining Room Light 1',	'00:17:88:01:06:c2:ea:51-0b'),
(44,	'Spare Room Ceiling 2',	'00:17:88:01:02:ba:ff:6f-0b'),
(51,	'Noise Machine',	'00:17:88:01:08:60:d1:83-0b');

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
  KEY `start_time` (`start_time`),
  KEY `end_time` (`end_time`),
  CONSTRAINT `light_history_ibfk_1` FOREIGN KEY (`light_id`) REFERENCES `lights` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2021-02-23 20:50:40