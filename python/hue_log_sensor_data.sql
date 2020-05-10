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
END