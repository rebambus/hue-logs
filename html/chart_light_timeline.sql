SELECT hue_lights.description row_label,
CASE state WHEN 1 THEN 'On'
        /*CASE WHEN brightness >= 254 THEN 'Max'
        WHEN brightness >= 200 THEN 'Bright'
        WHEN brightness >= 100 THEN 'On'
        WHEN brightness >= 0 THEN 'Dim'
        ELSE 'Off' END*/
  WHEN 0 THEN 'Off' ELSE 'Unavailable' END bar_label,
  json_date(CONVERT_TZ(CASE WHEN start_time > TIMESTAMPADD(HOUR,-24,UTC_TIMESTAMP()) THEN start_time
                            ELSE TIMESTAMPADD(HOUR,-24,UTC_TIMESTAMP()) END
                            ,'+00:00',@@global.time_zone)) start_time,
  json_date(CONVERT_TZ(end_time,'+00:00',@@global.time_zone)) end_time
FROM hue_light_history
JOIN hue_lights ON hue_lights.light_id = hue_light_history.light_id
LEFT JOIN
    (SELECT light_id,
          SUM(CASE WHEN state = 1 THEN TIMESTAMPDIFF(SECOND,start_time,end_time) ELSE 0 END) total_time_on,
          SUM(CASE WHEN state = 0 THEN TIMESTAMPDIFF(SECOND,start_time,end_time) ELSE 0 END) total_time_off
      FROM hue_light_history
      WHERE start_time > TIMESTAMPADD(HOUR,-24,UTC_TIMESTAMP())
      GROUP BY light_id
    ) light_summary ON light_summary.light_id = hue_light_history.light_id
WHERE end_time > TIMESTAMPADD(HOUR,-24,UTC_TIMESTAMP())
    AND state IS NOT NULL
  -- AND state=1
  -- AND start_time <> end_time
ORDER BY light_summary.total_time_on DESC, light_summary.total_time_off DESC;
