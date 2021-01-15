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

UPDATE lights
SET description = description
WHERE id = @light_id;

-- new
SELECT	@current_id := id,
	@current_state := state,
	@current_brightness := brightness,
	@current_reachable := reachable,
	@current_end_time := end_time
FROM light_history
WHERE light_id = @light_id
ORDER BY id DESC
LIMIT 1;

IF	@current_state = state
	AND @current_brightness = brightness
	AND @current_reachable = reachable
	AND @current_end_time >= DATE_ADD(UTC_TIMESTAMP(), INTERVAL -5 MINUTE) THEN
BEGIN
	UPDATE light_history
	SET end_time = UTC_TIMESTAMP()
	WHERE id = @current_id;
END
ELSE
BEGIN
	INSERT INTO light_history (light_id, start_time, end_time, state, brightness, reachable)
	VALUES (@light_id, UTC_TIMESTAMP(), UTC_TIMESTAMP(), state, brightness, reachable);
END IF;

END
