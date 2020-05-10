import requests
import json
import mysql.connector

url = 'http://192.168.86.172/api/E7aAajMAh3Uz5U39V2rCNuxuBmA3CJZVy31bF7rc/lights/2/state'
data = '{"on":true, "sat":254, "bri": 254, "hue": 40000}'

response = requests.put(url,data=data)

if (response.ok):
    print("OK")
    
    mydb = mysql.connector.connect(
        host = "localhost",
        user = "tom",
        passwd = "password",
        database = "tomdb"
        )
    
    mycursor = mydb.cursor()

    sql = ("SELECT"
"    ROUND(recent.value - hr_ago.value,1) hr_delta,"
"    CASE WHEN recent.value - hr_ago.value > 0 -- getting warmer"
"      THEN '{\"on\":true, \"sat\":254, \"hue\": 0}' -- red"
"    WHEN recent.value - hr_ago.value < 0 -- cooling off"
"      THEN '{\"on\":true, \"sat\":254, \"hue\": 40000}' -- blue"
"    END body"
"FROM (SELECT sensor, state,"
"     MAX(last_updated) recent,"
"     MAX(CASE WHEN last_updated < TIMESTAMPADD(HOUR,-1,UTC_TIMESTAMP()) THEN last_updated END) hr_ago"
"     FROM hue_data "
"WHERE sensor LIKE '%sensor 7' GROUP BY sensor) sensors"
"LEFT JOIN hue_data recent ON recent.sensor = sensors.sensor AND recent.state = sensors.state AND recent.last_updated = sensors.recent"
"LEFT JOIN hue_data hr_ago ON hr_ago.sensor = sensors.sensor AND hr_ago.state = sensors.state AND hr_ago.last_updated = sensors.hr_ago"
"WHERE sensors.state = 'temperature'"
"ORDER BY recent.value DESC;")
##    val = (lastupdated, sensor_name, measurand, value)
##    mycursor.execute(sql,val)
    mycursor.execute(sql)
            
    mydb.commit()
else:
    response.raise_for_status()