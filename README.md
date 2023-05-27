#  20190797 임동현 DYCP
컴퓨터 제어 프로그래밍      
컴퓨터제어프로래밍 수업을 요약, 정리하여 저장하는 레포지토리

## 외부 수집 시스템 등록 웹페이지
![image](https://github.com/Limdongdang/DYCP/assets/50188317/7b6324e8-2186-40db-97b0-7d5e95e476fe)
### 접속 주소 url : http://125.188.5.149:8797/iot/sht_register.html
## ID, NAME 지정 방법
ID 는 중복 방지를 위하여 학번으로 입력    
NAME은 원하는 이름 + (학번 끝자리 2자리) 입력  
**예시)**
| ID       | NAME         |
|----------|--------------|
| 20190797 | 내 집(97)    |
## url, graph 검증
**URL 검증:**
- url로 `current` 요청을 보내서 온도값과 습도값을 확인함 
- URL: `http://본인주소/iot/poc_pdo.php`

**Graph 검증:**
- graph의 url로 이미지를 불러와서 img 파일 검증.
- Graph: `http://본인주소/iot/sht_img.php`
- 파이썬으로 만든 이미지도 가능 
  - `단 실시간 그래프 확인을 위해선 파이썬 파일로 주기적으로 이미지를 갱신해야 함`  
- Graph: `http://본인주소/iot/pygraph.png`
## 정상 동작 검증
요청을 기다리고 응답이 완료되면 apikey 반환과 팝업과 함께 등록이 완료됨
![image](https://github.com/Limdongdang/DYCP/assets/50188317/299d9d3d-5bd6-4868-abad-a08d602f3bf4)

## 위도 경도 참고 사이트
https://jusoga.com/      
위 주소를 통해서 위도 경도 알아내기 가능
