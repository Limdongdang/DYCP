Edge server 만들기
======

apache 웹 서버
======
```
apache 웹 서버는 전 세계적으로 많은 웹 사이트에서 사용되고 있는 오픈소스 웹 서버 중 하나 , 안정성과 확장성이 높음
```

apache 설치 및 환경 설정
----
### 웹 서버 설치
```
$ sudo apt-get -y install apache2
``` 
### 웹 서버 루트 디렉토리 설정
> apache 웹 서버의 설정 파일인 000-default.conf 파일을 열기 위한 명령어      
> **000-default.conf 파일은 아파치 웹 서버의 기본 설정 파일이며, 이 파일을 수정하여 웹 서버의 동작 방식을 변경할 수 있음.**
```
$ sudo (편집기 툴) /etc/apache2/sites-enabled/000-default.conf

ex) $ sudo vi /etc/apache2/sites-enabled/000-default.conf
``` 


> 000-default.conf 파일 내용 수정을 통해 루트 디렉토리를 바꿀 수 있다.
```
DocumnetRoot (원하는 디렉토리 경로)

ex) DocumentRoot /var/www/html => DocumentRoot /var/www 
```

### 웹 서버 사용자 권한 확장
> 사용자에게 웹 서버의 필수 파일 및 디렉토리에 대한 액세스 권한을 부여하고 웹 서버에서 생성된 파일에 대한 쓰기 권한을 제공        
> 웹 서버 소프트웨어가 파일을 열거나 쓰기 위해 권한 부여
```
$ sudo usermod -a -G www-data pi
$ sudo usermod -a -G pi www-data 
```

### apache 웹 서버 재시작
> 웹 서버 설정 변경사항 적용을 위해 재시작
```
$ sudo systemctl restart apache2
```

## apache 웹 서버의 로그
> 웹 서버 로그는 /var/log/apache2 경로에 저장됨      
* error.log  : 웹 접속시 발생하는 오류 또는 개발자 로그 기록     
* access.log : 웹 페이지 접속 결과 제공
       
--------
> 터미널에서 실시간으로 로그를 확인하려면 tail -f ... 명령어를 쓴다.
* error.log 확인
```
$ sudo tail -f /var/log/apache2/access.log
```
* access.log 확인
```
$ sudo tail -f /var/log/apache2/error.log
```

## 웹 서버 시작 페이지 index.html
* 시작 페이지 
```
$ sudo vi /var/www/index.html
```
* index.html
```html
<html>
<body>
Raspberry pi
</body>
</html>
```
* 홈에 iot 디렉토리 만들기
```
$ mkdir ~pi/iot
```
* iot 디렉토리를 웹 서버 디렉토리와 symbolic link 연결

```
> 웹 서버에서 해당 디렉토리의 파일을 쉽게 읽고 쓸 수 있음
$ sudo ln -sfT ~pi/iot /var/www/iot
```

* 웹 페이지 테스트
```
http://[ip address]/iot/index.html
or
http://[ip address]/index.html
```
## php 서버
>PHP는 서버 사이드 스크립트 언어 
>HTML 코드 안에 PHP 코드를 넣을 수 있다. 이를 통해 동적인 웹 페이지를 생성하거나 데이터베이스에 접근하여 데이터를 처리하는 등의 작업을 수행할 수 있음

## json 파일
> 이름으로 데이터를 구분한 파일, 객체형 정보 저장에 용이하다.     
> 
![image](https://user-images.githubusercontent.com/50188317/226593071-e445a2c5-81da-4ac7-8700-2f738aef8930.png)

## json 파일을 php로 변환 코드
![image](https://user-images.githubusercontent.com/50188317/226593327-008ad7a2-c8f2-485c-b3f2-e13bae2e36da.png)
## json 파일을 js로 변환 코드
![image](https://user-images.githubusercontent.com/50188317/226593436-1ee0c3b0-12c3-4945-b0a0-44d7e3a37913.png)

## ajax 방식 문답 구조> 
> html 의 form 태그 대신 div 태그를 사용하여 표시한다.
> XMLHttpRequest를 사용하여 서버 호출
