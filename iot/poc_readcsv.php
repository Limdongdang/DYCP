<?php
$file = fopen('location.csv', 'r');

// 첫 번째 줄을 헤더로 읽어옵니다.
$header = fgetcsv($file);

$data = [];

while ($row = fgetcsv($file)) {
    // array_combine 함수를 사용하여 헤더와 각 행을 결합하여 key-value 배열을 생성합니다.
    $data[] = array_combine($header, $row);
}

fclose($file);

// 결과를 출력합니다.
print_r($data);
?>
