import time  
import requests  
import json  

print("Request data from server")  
urls='http://kodr.anhive.net/get_data.php'

try:
    while True:
        r = requests.get(urls)
        rt = json.loads(r.text)  
        temp = float(rt['data']['temperature'])  
        humi = float(rt['data']['humidity']) 
        # print("Temperature: %.2fC" %temp)  
        # print("Humidity: %.2f%%RH" %humidity)  
        if humi is not None and temp is not None:  
            print("Temp= {}*C Humidity= {}%".format(temp,humi))  
            url="http://223.194.166.221:19797/iot/sht.php"  
            data={'func':'create','t':temp,'h':humi}    
            requests.post(url,data=data)  
        else:  
            print("Failed to get reading.")
            
        time.sleep(5)
except KeyboardInterrupt:  
    pass