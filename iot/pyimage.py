import sqlite3
import matplotlib.pyplot as plt
import argparse

# ArgumentParser 객체 생성
parser = argparse.ArgumentParser()

# 입력받을 인자 설정
parser.add_argument('-f', '--filename', type=str, required=True, help='Output filename')

# 인자 파싱
args = parser.parse_args()

# SQLite 데이터베이스 연결
conn = sqlite3.connect('iot.db')

# 커서 생성
cur = conn.cursor()

# 쿼리 실행
cur.execute('SELECT time, temp, humi FROM ta_iot LIMIT 100')

# 결과 가져오기
results = cur.fetchall()

# x, y 데이터 추출
x = [result[0] for result in results]
y1 = [result[1] for result in results]
y2 = [result[2] for result in results]

# 그래프 그리기
fig, ax = plt.subplots()
ax.plot(x, y1, label='Temperature')
ax.plot(x, y2, label='Humidity')

# 레이블 및 타이틀 설정
ax.set_xlabel('Time')
ax.set_ylabel('Value')
ax.set_title('IoT Data')
ax.legend()

# 이미지 저장
fig.savefig(args.filename)

# 연결 종료
conn.close()