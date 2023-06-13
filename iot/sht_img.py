import sqlite3
import matplotlib.pyplot as plt

# SQLite 데이터베이스 연결
conn = sqlite3.connect('iot.db')
cursor = conn.cursor()

# 데이터 조회 쿼리 실행
query = "SELECT time, temp, humi FROM ta_iot LIMIT 100"
cursor.execute(query)
results = cursor.fetchall()

# 결과에서 시간, 온도 및 습도 추출
times = [result[0] for result in results]
temps = [result[1] for result in results]
humis = [result[2] for result in results]

# 그래프 생성
plt.plot(times, temps, label='Temperature')
plt.plot(times, humis, label='Humidity')
plt.xlabel('Time')
plt.ylabel('Value')
plt.title('IoT')
plt.legend()

# 습도 값 범위 수정
plt.ylim(bottom=0)  # 습도 값의 최소값을 0으로 설정

# 그래프를 파일로 저장
plt.savefig('iot_graph.png')

# SQLite 연결 종료
cursor.close()
conn.close()
