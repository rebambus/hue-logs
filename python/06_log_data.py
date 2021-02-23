import requests
import json
import mysql.connector
import time

mydb = mysql.connector.connect(
    host = "localhost",
    user = "python",
    passwd = "VHNvA3txrGRN3eU",
    database = "hue"
    )
mycursor = mydb.cursor()

while True:
    # Log Sensor Data
    url = 'http://76.190.227.168:16101/api/E7aAajMAh3Uz5U39V2rCNuxuBmA3CJZVy31bF7rc/sensors'
    data = ''

    response = requests.get(url,data=data)

    if (response.ok):
        jData = json.loads(response.content.decode('utf-8'))


        for key in jData:
            description = jData[key]['name']

            if 'uniqueid' in jData[key]:
                uniqueid = jData[key]['uniqueid']
            if 'lastupdated' in jData[key]['state']:
                lastupdated = jData[key]['state']['lastupdated']

            type = 'none'
            if 'temperature' in jData[key]['state']:
                    type = 'temperature'
                    value = jData[key]['state']['temperature']
                    if value is not None:
                         value = 1.0*value/100*9/5 + 32 # C*100 to F
            elif 'presence' in jData[key]['state']:
                    type = 'motion'
                    value = jData[key]['state']['presence']
            elif 'lightlevel' in jData[key]['state']:
                    type = 'lightlevel'
                    value = jData[key]['state']['lightlevel']

            if type != 'none' and lastupdated != 'none':
                args = [uniqueid, description, lastupdated, type, value]
                print(args)
                mycursor.callproc('hue_log_sensor_data',args)
                mydb.commit()
    else:
        response.raise_for_status()

    # Log Light Data
    url = 'http://76.190.227.168:16101/api/E7aAajMAh3Uz5U39V2rCNuxuBmA3CJZVy31bF7rc/lights'
    data = ''

    response = requests.get(url,data=data)

    if (response.ok):
        jData = json.loads(response.content.decode('utf-8'))

        for key in jData:
            uniqueid = jData[key]['uniqueid']
            description = jData[key]['name']
            state = jData[key]['state']['on']
            try:
                bri = jData[key]['state']['bri']
            except:
                bri = 0
            reachable = jData[key]['state']['reachable']

            if state == 0:
                bri = 0

            if reachable == 0:
                state = 0
                bri = 0

            args = [uniqueid, description, state, bri,reachable]
            print(args)
            mycursor.callproc('hue_record_light_history',args)
            mydb.commit()

    else:
        response.raise_for_status()

    time.sleep(5)
