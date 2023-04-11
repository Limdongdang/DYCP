## PDO를 이용한 데이터 입출력

### PDO(PHP Data Objects)

> PHP에서 데이터베이스와 상호작용하기 위한 데이터베이스 추상화 라이브러리

동일한 코드로 다양한 DB 사용 가능



코드 예시

```php
try {
    $db = new PDO("sqlite:iot:db")
    
    // 테이블 만들기
    if ($_POST["func"] == "table_create") {
    $db->exec("CREATE TABLE ta_iot(time NUMBER, addr TEXT, temp FLOAT, humi FLOAT)");
    die(json_encode("create table log"));
	}
    
    // CREATE DATA
if ($_POST["func"] == "insert" || $_POST["func"] == "create" ) {
    $t = $_POST["t"];
    $h = $_POST["h"];
    $address = $_SERVER['REMOTE_ADDR'];
    $time = date("YmdHis");

    $sql = <<<EOT
	INSERT INTO ta_iot(
        time, 
        addr, 
        temp, 
        humi
	) VALUES(
         :time,
         :addr,
         :temp,
         :humi
	)
	EOT;
    error_log($sql);
    # 데이터 일관성을 위한 트랜잭션 지점 설정
    $db->beginTransaction();
    $stmt = $db->prepare($sql);
    #값을 대입후 execute를 통해 수행
    $r = $stmt->execute([
            ':time' => $time,
            ':addr' => $address,
            ':temp' => $t,
            ':humi' => $h,
    ]);
    // 모든 데이터베이스 연산이 정상적으로 실행되면 커밋
    $db->commit();
    die(json_encode("insert count:".$r));
}
} catch(PDOException $e) {
    // 예외가 발생하면 롤백
    $db->rollback();
    
    // 예외 처리
    echo "Error: " . $e->getMessage();
}
```



1. DB와 테이블 생성

   1. ```bash
      $ sqlite3 ~/iot/iot.db
      ```

      iot.db로 DB 생성

2. PHP 코드 작성

   1. sqlite로 만든 iot.db 사용

      ```PHP
      $db = new PDO("sqlite:iot:db")
      ```

      

   2. exec함수를 통한 sql table 생성 구문 

      ```php
      $db->exec("CREATE TABLE ta_iot(time NUMBER, addr TEXT, temp FLOAT, humi FLOAT)");
      ```

      

   3. PDO를 사용하여 SQL 쿼리를 생성 SQL 문장을 변수로 지정할 수 있다.

      ```php
      $sql = <<<EOT
      INSERT INTO ta_iot(
       time,
       addr,
       temp,
       humi
      ) VALUES(
       :time,
       :addr,
       :temp,
       :humi
      )
      EOT;
      ```

   4. 데이터일관성을 위한 트랜잭션 지점 설정

      ```php
      $db->beginTransaction(); 
      ```

   5. SQL문장 분석 및 데이터 처리 준비

      ```php
      $stmt = $db->prepare($sql);
      ```

   6. 변수에 값을 지정

      ```php
      $r = $stmt->execute([
       ':time' => $time,
       ':addr' => $address,
       ':temp' => $t,
       ':humi' => $h,
      ]);
      ```

   7. 데이터 처리 종료 확인 실패 시 rollback 가능

      ```php
      $db->commit();
      ```