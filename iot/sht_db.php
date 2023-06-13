<?php
$db = new SQLite3('iot.db');
/*
$res = $db->query("select count(*) as cnt from sqlite_master where
name = 'ta_iot'");
$row = $res->fetchArray();
error_log("count = ". $row['cnt']);
if ($row['cnt']==0) {
 $db->exec("CREATE TABLE ta_iot(time NUMBER, addr TEXT, temp
FLOAT, humi FLOAT)");
}
*/
// CREATE DATA
if ($_POST["func"] == "insert" ||
 $_POST["func"] == "create") {
 $t = $_POST["t"];
 $h = $_POST["h"];
 $address = $_SERVER['REMOTE_ADDR'];
 $time = date("YmdHis");

 $db->exec("INSERT INTO ta_iot(time, addr, temp, humi)
VALUES(".$time.",'".$address."', ".$t.", ".$h.")"); die(json_encode("insert data:".$t."/".$h));
}
// RETRIEVE DATA
if ($_POST["func"] == "select") {
 $s = isset($_POST["jumpto"])?$_POST["jumpto"]:0;
 $c = isset($_POST["count"])?$_POST["count"]:10;
 $time =
isset($_POST["date"])||$_POST["date"]==""?$_POST["date"]:date('Ymd')
;;
 $sql = <<<EOT
SELECT *
 FROM ta_iot
WHERE time like '$time%'
 LIMIT $c
OFFSET $s
EOT;
 $r = array();
 $res = $db->query($sql); while ($row = $res->fetchArray()) { $r[] = $row;
  error_log(print_r($row, true)); } die(json_encode($r));
}
// UPDATE DATA
if ($_POST["func"] == "update") { $t = $_POST["t"]; $h = $_POST["h"]; $address = $_SERVER['REMOTE_ADDR']; $otime = $_POST["time"]; $time = date("YmdHis");
 $sql = <<<EOT
UPDATE ta_iot SET
 time = $t,
 humi = $h,
 addr = '$address'
 WHERE time = '$otime'
EOT;
 error_log($sql); $r = $db->exec($sql); die(json_encode("update count:".$r));
}
// DELETE DATA
if ($_POST["func"] == "delete") { $otime = $_POST["time"];
 $sql = <<<EOT
DELETE from ta_iot
 WHERE time = '$otime'
EOT;
 error_log($sql); $r = $db->exec($sql); die(json_encode("delete count:".$r));
}
error_log($_POST["func"]." is not exist");
die($_POST["func"]." is not exist");
?>