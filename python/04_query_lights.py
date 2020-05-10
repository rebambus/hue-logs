import requests
import json
import mysql.connector

url = 'http://192.168.86.172/api/E7aAajMAh3Uz5U39V2rCNuxuBmA3CJZVy31bF7rc/lights'
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
        uniqueid = jData[key]['uniqueid']
        description = jData[key]['name']
        state = jData[key]['state']['on']
        try:
            bri = jData[key]['state']['bri']
        except:
            bri = 0
        reachable = jData[key]['state']['reachable']

        args = [uniqueid, description, state, bri,reachable]
        print(args)
        mycursor.callproc('hue_record_light_history',args)
        mydb.commit()

else:
    response.raise_for_status()
