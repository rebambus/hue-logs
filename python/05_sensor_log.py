import requests
import json
import mysql.connector

url = 'http://192.168.86.172/api/E7aAajMAh3Uz5U39V2rCNuxuBmA3CJZVy31bF7rc/sensors'
data = ''

response = requests.get(url,data=data)

if (response.ok):
    jData = json.loads(response.content.decode('utf-8'))

    #pretty print?
    #print(json.dumps(jData, indent=4, sort_keys=True))
    #print(jData)

    mydb = mysql.connector.connect(
        host = "localhost",
        user = "tom",
        passwd = "password",
        database = "tomdb"
        )

    mycursor = mydb.cursor()

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
                value = 1.0*value/100*9/5 + 32 # C*100 to F
        elif 'presence' in jData[key]['state']:
                type = 'motion'
                value = jData[key]['state']['presence']
        elif 'lightlevel' in jData[key]['state']:
                type = 'lightlevel'
                value = jData[key]['state']['lightlevel']

        if type != 'none':
            args = [uniqueid, description, lastupdated, type, value]
            print(args)
            mycursor.callproc('hue_log_sensor_data',args)
            mydb.commit()
else:
    response.raise_for_status()
