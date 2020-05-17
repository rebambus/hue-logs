SELECT   lights.description AS row_label,
         CASE WHEN state = 1
                  THEN 'On'
              WHEN state = 0
                  THEN 'Off'
              ELSE 'Unavailable'
         END AS bar_label,
         UNIX_TIMESTAMP(CASE WHEN start_time > TIMESTAMPADD(HOUR, -24, UTC_TIMESTAMP())
                                 THEN start_time
                             ELSE TIMESTAMPADD(HOUR, -24, UTC_TIMESTAMP())
                        END) start_time,
         UNIX_TIMESTAMP(end_time) end_time
FROM     light_history AS log
         JOIN lights
             ON lights.id = log.light_id
         LEFT JOIN(   SELECT   light_id,
                               SUM(CASE WHEN state = 1
                                            THEN TIMESTAMPDIFF(SECOND, start_time, end_time)
                                        ELSE 0
                                   END) total_time_on,
                               SUM(CASE WHEN state = 0
                                            THEN TIMESTAMPDIFF(SECOND, start_time, end_time)
                                        ELSE 0
                                   END) total_time_off
                      FROM     light_history
                      WHERE    start_time > TIMESTAMPADD(HOUR, -24, UTC_TIMESTAMP())
                      GROUP BY light_id) light_summary
             ON light_summary.light_id = log.light_id
WHERE    end_time > TIMESTAMPADD(HOUR, -24, UTC_TIMESTAMP())
    AND (state IS NULL OR state = 1)
-- AND state=1
-- AND start_time <> end_time
ORDER BY light_summary.total_time_on DESC, light_summary.total_time_off DESC;
