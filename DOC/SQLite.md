## SQLite

RDB(관계형 데이터베이스) 라이브러리 표준 SQL 구문을 이용하여 데이터 입출력

소규모 웹, 앱에서 다량 데이터를 저장하는데 용이함

## SQLite로 DB파일 만들기



### 1. SQLite 설치

```shell
$ sudo apt -y install sqlite3 php-sqlite3
```

### 2. 설치 확인

```shell
$ sqlite3
```

### 3. sqlite3로 데이터베이스 파일 만들기

```shell
$ sqlite3 ~/filename.db
```

### 4. sqlite 명령어 예시

- 테이블 생성

```sqlite
create table ta(no number,name text);
```

- 테이블 정보 조회

```sqlite
.schema ta
```

- 데이터 입력

```sqlite
insert into ta(no, name) values (1, "sam");
```

- 데이터 조회

```sqlite
select * from ta;
```



## SQLite와 PHP파일 연결

php에서 사용할 수 있도록 라이브러리를 설치해야 한다.

```shell
$ sudo apt -y install php-sqlite3
```



php파일로 insert 구문 실행

```php
$db = new SQLite3('iot.db');

$db->exec("INSERT INTO ta_iot(time, addr, temp, humi)
VALUES(".$time.",'".$address."', ".$t.", ".$h.")");
```



## PDO와 SQLite의 차이점

SQLite는 경량 데이터베이스 관리 시스템으로 파일 기반의 데이터 베이스이다.

모바일기기나 소규모의 웹 어플리케이션 에서 자주 사용되며 데이터베이스로 지정한 파일을

사용 해 간편하게 데이터베이스의 복사와 이동이 자유롭다



하지만 이러한 파일 시스템 덕분에 보안상 취약점도 많다 이를 위해 PDO를 사용한다

PDO라이브러리를 통해 SQLite에서 빈약했던 인증과 암호화 기능을 사용해 Injection 공격을 예방할

수 있다.

