SELECT CONCAT('[new Date(''', CONVERT_TZ(time_stamp, '+00:00', '-04:00'), '''),',
	IFNULL(ROUND(back_porch,2),'null'), ',',
	IFNULL(ROUND(front_porch,2),'null'), ',',
  IFNULL(ROUND(basement,2),'null'), ',',
  IFNULL(ROUND(upstairs_hallway,2),'null'), ',',
  IFNULL(ROUND(bathroom,2),'null'), ',',
  IFNULL(ROUND(front_hallway,2),'null'),
  '],') chart_row -- list with 7 elements
FROM temperature_hourly
WHERE lastupdated_min > TIMESTAMPADD(HOUR,-6,UTC_TIMESTAMP())
ORDER BY time_stamp;
