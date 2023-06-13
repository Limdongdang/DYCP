import smbus2
import time
import requests
bus = smbus2.SMBus(1)
bus.write_i2c_block_data(0x44, 0x22, [0x36])
while True:
     time.sleep(1) 
     data = bus.read_i2c_block_data(0x44, 0x00, 6)
     temp_r = data[0] * 256 + data[1]
     temp = round(-45 + (175 * temp_r / 65535.0), 1)
     humi = round(100 * (data[3] * 256 + data[4]) / 65535.0, 1) 
     # Output data 
     if humi is not None and temp is not None: print ("Temp= { }*C Humidity= { }%".format(temp, humi))
        url="http://localhost/iot/sht_db.php"
        data= {'func':'create', 't':temp, 'h':humi}
        requests.post(url, data= data) 
     else: 
        print ("Failed to get reading.")
     time.sleep(29)