import requests
import json
import mysql.connector

url = 'http://76.190.227.168:16101/api/E7aAajMAh3Uz5U39V2rCNuxuBmA3CJZVy31bF7rc/sensors'
data = ''

response = requests.get(url,data=data)

if (response.ok):
    jData = json.loads(response.content.decode('utf-8'))
    
    #pretty print?
    #print(json.dumps(jData, indent=4, sort_keys=True))
    #print(jData)
    
    mydb = mysql.connector.connect(
        host = "localhost",
        user = "python",
        passwd = "VHNvA3txrGRN3eU",
        database = "hue"
        )
    
    mycursor = mydb.cursor()
    
    for key in jData:
        if "temperature" in jData[key]['name']:
            sensor_name = jData[key]['name']
            lastupdated = jData[key]['state']['lastupdated']
            temp = jData[key]['state']['temperature']
            temp = 1.0*temp/100*9/5 + 32 # C*100 to F
            
            print ("{} {} {}".format(sensor_name,lastupdated,temp))
            
            sql = "INSERT IGNORE INTO hue_temp_data (sensor_name, lastupdated, temperature) VALUES (%s, %s, %s)"
            val = (sensor_name, lastupdated, temp)
            mycursor.execute(sql,val)
            
    mydb.commit()
else:
    response.raise_for_status()
