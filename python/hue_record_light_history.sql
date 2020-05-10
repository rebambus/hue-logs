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
END
