<?php
date_default_timezone_set('Asia/Seoul');

define ("DBFILE", "iot.db");
if (!file_exists(DBFILE)) {
    touch(DBFILE);
    chmod(DBFILE, "g+w");
}
$db = new PDO("sqlite:".DBFILE);

function tableExists($pdo, $table) {
    try {
        $result = $pdo->query("SELECT 1 FROM {$table} LIMIT 1");
    } catch (Exception $e) {
        return FALSE;
    }
    return $result !== FALSE;
}

if (!tableExists($db, "ta_iot")) {
    $db->exec("CREATE TABLE ta_iot(time NUMBER, addr TEXT, temp FLOAT, humi FLOAT)");
    $db->exec("alter table ta_iot add timestamp NUMBER");
    $db->exec("UPDATE ta_iot SET
    timestamp = strftime('%s',
         substr(time,1,4)||'-'||
         substr(time,5,2)||'-'||
         substr(time,7,2)||' '||
         substr(time,9,2)||':'||
         substr(time,11,2)||':'||
         substr(time,13,2))
");
    $db->exec("create INDEX ta_iot_tt on ta_iot(time)");
    $db->exec("create INDEX ta_iot_ts on ta_iot(timestamp)");
}

if (!tableExists($db, "ta_station")) {
    $db->exec("CREATE TABLE ta_station(id TEXT, name TEXT, 
                               address TEXT, latitude NUMBER, 
                               longitude NUMBER, url TEXT, graph TEXT, 
                               apikey TEXT, PRIMARY KEY(id) )");
    $db->exec("create INDEX ta_station_key on ta_station(key)");
    $db->exec("create INDEX ta_station_id  on ta_station(id)");
}

if ($_POST["func"] == "table_drop") {
    $db->exec("DROP TABLE ta_iot");
    $db->exec("DROP TABLE ta_station");
    die(json_encode("create table log"));
}

// CREATE DATA
if (strpos(';create;insert;', ";".$_POST["func"].";")!==false) {
    $t = $_POST["t"];
    $h = $_POST["h"];
    $address = $_SERVER['REMOTE_ADDR'];
    $time = date("YmdHis");
    error_log("LOG time:".$time);

    $sql = <<<EOT
INSERT INTO ta_iot(
    time,
    addr,
    temp,
    humi,
    timestamp
) VALUES(
     :time,
     :addr,
     :temp,
     :humi,
     :timestamp
)
EOT;
    error_log($sql);
    $db->beginTransaction();
    try {
        $stmt = $db->prepare($sql);
        $param = array();
        $param[':time'   ] = $time;
        $param[':addr'   ] = $address;
        $param[':temp'   ] = $t;
        $param[':humi'   ] = $h;
        $param[':timestamp'   ] = time();
        error_log(print_r($param, true));
        $r = $stmt->execute($param);
        error_log("insert count:".$r.">>>".print_r($db->errorInfo(), true));
    } catch (Exception $e) {
        $msg = "\nUpdate error:".$sql."\n"
              .print_r($db->errorInfo(), true);
        $msg .= print_r($e->getMessage(), true);
        $db->rollback();
        error_log($msg);
    }
    $db->commit();
    die(json_encode("insert count:".$r));
}

// RETRIEVE DATA
if ($_POST["func"] == "select") {
    $s = isset($_POST["jumpto"])?$_POST["jumpto"]:0;
    $c = isset($_POST["count"])?$_POST["count"]:10;
    $time = isset($_POST["date"])?$_POST["date"]:date('Ymd');;

    $sql = <<<EOT
SELECT *
  FROM ta_iot
WHERE time like :time
order by time DESC
 LIMIT :count
OFFSET :jumpto
EOT;
    $r = array();
    error_log($sql);
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':count' ,$c);
    $stmt->bindValue(':jumpto' ,$s);
    $stmt->bindValue(':time' ,$time."%");
    $stmt->execute();

    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
    die(json_encode($r));
}

// CURRENT DATA
if ($_POST["func"] == "current") {
    $sql = <<<EOT
SELECT *
  FROM ta_iot
ORDER by time DESC
 LIMIT 1
EOT;
    $r = array();
    error_log($sql);
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
    die(json_encode($r));
}

// CURRENT DATA
if ($_POST["func"] == "pyimage") {

    if (! file_exists("pyimage.py")) {
        $pycode = <<<EOP
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
EOP;
        file_put_contents("pyimage.py", $pycode);
    }

    $imagepath = "pygraph.png";
    if (! file_exists($imagepath) || (time() - filectime($imagepath) > 60)) {
        $out = shell_exec("python pyimage.py -f pygraph.png");
    }

    $sql = <<<EOT
SELECT *
  FROM ta_iot
ORDER by time DESC
 LIMIT 1
EOT;
    $r = array();
    error_log($sql);
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $r[0]['graph'] = $imagepath;
    die(json_encode($r));
}


// UPDATE DATA
if ($_POST["func"] == "update") {
    $t = $_POST["t"];
    $h = $_POST["h"];
    $address = $_SERVER['REMOTE_ADDR'];
    $otime = $_POST["time"];

    $sql = <<<EOT
UPDATE ta_iot SET
    temp = :temp,
    humi = :humi,
    addr = :addr
 WHERE time = :time
EOT;
    error_log($sql);
    $db->beginTransaction();
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        error_log("\nPDO::errorInfo() for prepare:\n");
        error_log(print_r($db->errorInfo(), true));
    }
    $stmt->bindValue(':temp' ,$t);
    $stmt->bindValue(':humi' ,$h);
    $stmt->bindValue(':addr' ,$address);
    $stmt->bindValue(':time' ,$otime);

    $r = $stmt->execute();
    $db->commit();
    die(json_encode("update count:".$r));
}

// DELETE DATA
if ($_POST["func"] == "delete") {
    $otime = $_POST["time"];

    $sql = <<<EOT
DELETE from ta_iot
 WHERE time = :time
EOT;
    error_log($sql);
    $db->beginTransaction();
    $stmt = $db->prepare($sql);
    $r = $stmt->execute([
            ':time' => $otime
    ]);
    $db->commit();
    die(json_encode("delete count:".$r));
}


// UPDAE DATA
if ($_POST["func"] == "update_list") {
    $rows = json_decode($_POST['json'], true);

    $sql_insert = <<<'SQLSTATEMENT'
INSERT INTO ta_iot(
    time,
    addr,
    temp,
    humi,
    timestamp
) VALUES(
     :time,
     :addr,
     :temp,
     :humi,
     :timestamp
);
SQLSTATEMENT;

    $sql_update = <<<'SQLSTATEMENT'
UPDATE ta_iot SET
    temp = :temp,
    humi = :humi,
    addr = :addr
 WHERE time = :time
SQLSTATEMENT;

    $count=0;
    $db->beginTransaction();
    foreach($rows as $row) {
        $sql = ($row['time']!="")?$sql_update:$sql_insert;
        $stmt = $db->prepare($sql);
        $param = array();
        $param[':temp'    ] = $row['temp'     ];
        $param[':humi'    ] = $row['humi'   ];
        $param[':addr'    ] = $row['addr'    ];
        if ($row['time']!="") {
            $param[':time'] = $row['time'];
            $param[':timestamp'] = time();
        } else {
            $param[':time'] = date("YmdHis");
        }
        $param[':addr'    ] = $row['addr'    ];
        $r = $stmt->execute($param);
        if (!$r) {
            $msg = "\nUpdate error:".$sql."\n"
                  .print_r($db->errorInfo(), true);
            $db->rollback();
            error_log($msg);
            die($msg);
        }
        $count += $r;
    }
    $db->commit();
    die(json_encode("insert&update count:".$count++));
}

if ($_POST["func"] == "graph") {
    $date_begin = (isset($_POST["date_begin"])
                     ?$_POST["date_begin"]:date('Ymd')).'000000';
    $date_end   = (isset($_POST["date_end"])
                     ?$_POST["date_end"]:date('Ymd')).'235959';

    $sql = <<<EOT
SELECT time, humi, temp
  FROM ta_iot
WHERE time between :date_begin and :date_end;
EOT;

    $r = array();
    error_log($sql);
    $stmt = $db->prepare($sql);
    $param = array();
    $param[':date_begin' ] = $date_begin;
    $param[':date_end'   ] = $date_end;
    $stmt->execute($param);

    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
    die(json_encode($r));
}


/* 테이블 변경 & 데이터 업데이트
alter table ta_iot add timestamp NUMBER;
UPDATE ta_iot SET
    timestamp = strftime('%s',
         substr(time,1,4)||'-'||
         substr(time,5,2)||'-'||
         substr(time,7,2)||' '||
         substr(time,9,2)||':'||
         substr(time,11,2)||':'||
         substr(time,13,2))
create UNIQUE INDEX ta_iot_ts on ta_iot(timestamp);
create        INDEX ta_iot_tt on ta_iot(time);
*/

// AVERAGE
if ($_POST["func"] == "average") {
    $date_begin = (isset($_POST["date_begin"])?$_POST["date_begin"]:date('Ymd')).'000000';;
    $date_end   = (isset($_POST["date_end"])?$_POST["date_end"]:date('Ymd')).'235959';;
    $interval   = isset($_POST["interval"])?$_POST["interval"]:300;;

    error_log(":::".$date_begin.">:".$date_end.">:".$interval);

    $sql = <<<EOT
SELECT strftime('%Y:%m:%d %H:%M:%S', span, 'unixepoch', 'localtime') as time,
        humi, temp, cnt
FROM (
      SELECT span, avg(humi) humi, avg(temp) temp, count(*) cnt
      FROM (
            SELECT CAST(timestamp/:interval as INT)*:interval as span,
               humi, temp
              FROM ta_iot
             WHERE time between :date_begin and :date_end )
       group by span)
EOT;

    $r = array();
    error_log($sql);
    $stmt = $db->prepare($sql);

    $param = array();
    $param[':date_begin' ] = $date_begin;
    $param[':date_end'   ] = $date_end;
    $param[':interval'   ] = $interval;
    $stmt->execute($param);

    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
    die(json_encode($r));
}

// read cvs file in form of key and value
if ($_POST["func"] == "location") {
    $jumpto = isset($_POST["jumpto"])?$_POST["jumpto"]:0;
    $batch  = isset($_POST["count"])?$_POST["count"]:100;
    $search = isset($_POST["key"])?$_POST["key"]:"";
    $file = 'location.csv'; // Replace with the path to your CSV file

    $data = [];
    //$data[] = array ("id"=>"ID01","name"=>"L01","latitude"=>38,"longitude"=>126.5);
    $cnt = 0;
    if (($handle = fopen($file, 'r')) !== false) {
        $headers = fgetcsv($handle); // Read the first line as headers
        while (($row = fgetcsv($handle)) !== false) {

            if ($search != "") {
                $find = false;
                foreach($row as $k => $v) {
                    error_log("value: [$v]");
                    if (strpos($v, $search) !== false) { $find = true; break;}
                }
                if ($find == false) {
                    error_log("not found key: [$search]");
                    continue;
                }
            }
            $cnt +=1;
            if ($cnt < $jumpto+1) {
                //error_log("skip to : $jumpto [$cnt]");
                continue;
            }
            $rowData = array_combine($headers, $row); // Combine headers with row values
            $data[] = $rowData; // Add the transformed row to the data array
            if ($cnt >= ($jumpto+$batch))  break;
        }
        fclose($handle);
    }

    $r = array("location"=>$data);
    //error_log(json_encode($r));
    die(json_encode($r));
}

//외부 수집 시스템 등록 내용 조회
if (strpos('list_station', $_POST["func"])!==false) {
    $count = isset($_POST["count"])?$_POST["count"]:20;

    $sql = "SELECT * from ta_station LIMIT :count";

    $stmt = $db->prepare($sql);
    $param = array();
    $param[':count'] = $count;
    
    $stmt->execute($param);
    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
    die(json_encode($r));
}

// UPDAE DATA
//id TEXT, name TEXT, address TEXT, latitude NUMBER, longitude NUMBER, url TEXT, apikey TEXT
if ($_POST["func"] == "update_station") {
    $rows = json_decode($_POST['json'], true);


    $sql_select = <<<'SQLSTATEMENT'
SELECT id FROM ta_station WHERE id = :id;
SQLSTATEMENT;

    $sql_insert = <<<'SQLSTATEMENT'
INSERT INTO ta_station(
    id,
    name,
    address,
    latitude,
    longitude,
    url,
    graph,
    apikey
) VALUES(
     :id,
     :name,
     :address,
     :latitude,
     :longitude,
     :url,
     :graph,
     :apikey
);
SQLSTATEMENT;

    $sql_update = <<<'SQLSTATEMENT'
UPDATE ta_station SET
    id = :id,
    name = :name,
    address = :address,
    latitude = :latitude,
    longitude = :longitude,
    url = :url,
    graph = :graph
 WHERE apikey = :apikey
SQLSTATEMENT;

    $count=0;
    $db->beginTransaction();
    foreach($rows as $row) {
        $param = array();
        $param[':id'        ] = $row['id'       ];
        $param[':name'      ] = $row['name'     ];
        $param[':address'   ] = $row['address'  ];
        $param[':latitude'  ] = $row['latitude' ];
        $param[':longitude' ] = $row['longitude'];
        $param[':url'       ] = $row['url'      ];
        $param[':graph'     ] = $row['graph'    ];
        
        $string = $row['graph'];
        $pattern = '/src="([^"]+)"/';
        preg_match($pattern, $string, $matches);

        if (!empty($matches[1])) {
            $param[':graph'     ] = $matches[1];
        }
        
        $sql = $sql_insert;
        if ($row['apikey']!="") {
            $sql = $sql_update;
            $param[':apikey'   ] = $row['apikey'  ];
        } else {
            $param[':apikey'   ] 
              = md5($row['name']."::".$row['address']."::".$row['url']);
        }
        $stmt = $db->prepare($sql);
        $r = $stmt->execute($param);
        if (!$r) {
            $db->rollback();
            $msg = print_r($stmt->errorInfo(), true);
            error_log($msg);
            die(json_encode($stmt->errorInfo(), true));
        }
        $count += $r;
    }
    $db->commit();
    die(json_encode("insert&update count:".$count++));
}

// DELETE DATA
if ($_POST["func"] == "delete_station") {
    $apikey = $_POST["apikey"];

    $sql = <<<EOT
DELETE from ta_station
 WHERE apikey = :apikey
EOT;
    error_log($sql);
    $db->beginTransaction();
    $stmt = $db->prepare($sql);
    $r = $stmt->execute([
            ':apikey' => $apikey
    ]);
    $db->commit();
    die(json_encode("delete count:".$r));
}

error_log($_POST["func"]." is not exist");
die($_POST["func"]." is not exist");

?>