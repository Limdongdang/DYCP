
const canvas = document.getElementById("chart");
const ctx = canvas.getContext("2d");
var tempData = [1,2,3,4,5];
var humiData = [1,2,3,4,5];
var timelabel = ["A", "B", "C", "D", "E"];
var hour = 0;
var minute = 0;
var second = 0;
const date = new Date
// marker3 팝업에 대한 라인 그래프를 그리는 함수
function drawChart() {

  // 임의의 초기 데이터
  

  // 그래프 그리기
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  // 라인 그래프 생성
  new Chart(ctx, {
    type: "line",
    data: {
      labels: timelabel, // X축 라벨
      datasets: [
        {
          label: "온도",
          data: tempData,
          borderColor: "red",
          backgroundColor: "transparent",
        },
        {
          label: "습도",
          data: humiData,
          borderColor: "blue",
          backgroundColor: "transparent",
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
    },
  });
}

// marker3 팝업의 내용을 업데이트하고 라인 그래프를 그립니다.
function updatePopupContent() {
	var data = new FormData();
	data.append('func','current');
	var request = new XMLHttpRequest();
	request.onreadystatechange = function(){  
		if(request.readyState==4){  
			var resp;
			try{  
				resp = JSON.parse(request.response);
			} catch (e) {
				resp = request.response
				+' error:Unknown error occurred' ;
				
			}
			console.log(resp[0].temp);
			console.log(resp[0].humi);
			hour = resp[0].time.substring(8, 10);
			minute = resp[0].time.substring(10, 12);
			second = resp[0].time.substring(12, 14);
			console.log(hour + "시 " + minute + "분 " + second + "초");
			timelabel.push(hour + "시 " + minute + "분 " + second + "초");
			timelabel.shift();
			console.log(tempData);
			console.log(humiData);
			tempData.push(resp[0].temp);
			humiData.push(resp[0].humi);
			tempData.shift();
			humiData.shift();
			drawChart();
		}
	};
	request.open('POST', "sht_pdo.php");
	request.send(data);
	
}

setInterval(updatePopupContent, 2000);