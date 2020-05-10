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
        if jData[key]['type'] == "ZLLTemperature":
            sensor_name = jData[key]['name']
            lastupdated = jData[key]['state']['lastupdated']
            measurand = 'temperature'
            value = jData[key]['state'][measurand]
            value = 1.0*value/100*9/5 + 32 # C*100 to F
            
            print ("{} {} {} {}".format(lastupdated,sensor_name,measurand,value))
            
            sql = "INSERT IGNORE INTO hue_data (last_updated, sensor, state, value) VALUES (%s, %s, %s, %s)"
            val = (lastupdated, sensor_name, measurand, value)
            mycursor.execute(sql,val)
            
        if jData[key]['type'] == "ZLLPresence":
            sensor_name = jData[key]['name']
            lastupdated = jData[key]['state']['lastupdated']
            measurand = 'presence'
            value = jData[key]['state'][measurand]
                
            print ("{} {} {} {}".format(lastupdated,sensor_name,measurand,value))
            
            sql = "INSERT IGNORE INTO hue_data (last_updated, sensor, state, value) VALUES (%s, %s, %s, %s)"
            val = (lastupdated, sensor_name, measurand, value)
            mycursor.execute(sql,val)
            
        if jData[key]['type'] == "ZLLLightLevel":
            sensor_name = jData[key]['name']
            lastupdated = jData[key]['state']['lastupdated']
            measurand = 'lightlevel'
            value = jData[key]['state'][measurand]
                
            print ("{} {} {} {}".format(lastupdated,sensor_name,measurand,value))
            
            sql = "INSERT IGNORE INTO hue_data (last_updated, sensor, state, value) VALUES (%s, %s, %s, %s)"
            val = (lastupdated, sensor_name, measurand, value)
            mycursor.execute(sql,val)
            
    mydb.commit()
else:
    response.raise_for_status()
