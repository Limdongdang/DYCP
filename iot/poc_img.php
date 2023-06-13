<?php
// Open SQLite database
$time_start = microtime(true);

$db = new SQLite3('iot.db');


$sid = isset($_REQUEST['id'])?$_REQUEST['id']:"00000000";
if ($sid != '00000000') {
    $r = $db->query("SELECT url from ta_station WHERE id = '".$sid."';");
    $row = $r->fetchArray();
    if ( count($row) > 0 and  $row['url'] != "") {
        error_log("-------------------------".$row['url']);
        $term = 14;
        $postdata = http_build_query(
            array(
                'func' => 'average',
                "interval" => 24*$term*3600/100, 
                "date_begin" => "".date('Ymd', strtotime("-$term day")),
                "date_end" => "".date('Ymd')
            )
        );

        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );
        $context  = stream_context_create($opts);
        $result = file_get_contents($row['url'], false, $context);
        error_log("==============".$result);
        
        $result = json_decode($result, true);
        $years = array();
        $temp = array();
        $humi = array();        
        forEach ($result as $row) {
            $years[] = $row['time'];
            $temp[] = $row['temp'];
            $humi[] = $row['humi'];
        }            
        $data = [$temp, $humi];
    }
} else {
    $title_id = isset($_REQUEST['id'])?$_REQUEST['id']:"Graph";
    // Query database for data
    $sql = <<<EOT
SELECT strftime('%H:%M', span, 'unixepoch', 'localtime') as time,
        humi, temp, cnt 
FROM (
      SELECT span, avg(humi) humi, avg(temp) temp, count(*) cnt
      FROM ( 
            SELECT CAST(timestamp/:interval as INT)*:interval as span,
               humi, temp
              FROM ta_iot 
             WHERE time between :date_begin and :date_end) 
       group by span)
EOT
;

    $term = 14; //days
    $sql = str_replace(":interval", "".(24*$term*3600/100), $sql);
    $sql = str_replace(":date_begin", date('Ymd', strtotime("-$term day")).'000000', $sql);
    $sql = str_replace(":date_end", date('Ymd').'235959', $sql);

    $results = $db->query($sql);

    // Fetch data into arrays
    $years = array();
    $temp = array();
    $humi = array();
    while ($row = $results->fetchArray()) {
        $years[] = $row['time'];
        $temp[] = $row['temp'];
        $humi[] = $row['humi'];
    }
    $data = [$temp, $humi];
}
// Close database
$db->close();

// Image dimensions
$img_width = 600;
$img_height = 400;

// Create image resource
$image = imagecreatetruecolor($img_width, $img_height);

// Set colors
$bg_color = imagecolorallocate($image, 255, 255, 255);
$axis_color = imagecolorallocate($image, 0, 0, 0);
$test_color = imagecolorallocate($image, 123, 0, 222);
$sub_color = imagecolorallocate($image, 220, 220, 220);
$line_colors = array(imagecolorallocate($image, 255, 0, 0), // temp
                     imagecolorallocate($image, 0, 255, 0)); // humi
$legend_colors = array('Temp', 'Humi');
$label_color = imagecolorallocate($image, 0, 0, 0);
$title_color = imagecolorallocate($image, 255, 0, 0);

// Draw background
imagefilledrectangle($image, 0, 0, $img_width, $img_height, $bg_color);

// Set padding and margins
$padding = 30;
$y_margin = 30;
$x_margin = 50;

// Calculate plot dimensions
$plot_width = $img_width - 2 * $padding - $x_margin;
$plot_height = $img_height - 2 * $padding - $x_margin;

// Find max value in data
$max_value = max(array_merge($temp, $humi));
$min_value = min(array_merge($temp, $humi))-5;
$max_range = $max_value - $min_value;

// Calculate y-axis label interval
$y_axis_interval = ceil($max_value / 7);

// Draw x-axis and y-axis
imageline($image, $padding + $x_margin, 
                  $img_height - $padding - $y_margin, 
                  $padding + $x_margin + $plot_width, 
                  $img_height - $padding - $y_margin, 
                  $axis_color);
imageline($image, $padding + $x_margin, 
                  $img_height - $padding - $y_margin, 
                  $padding + $x_margin, 
                  $padding + $y_margin/2, 
                  $test_color);

// Draw y-axis labels
for ($i = $min_value; $i <= $max_value; $i += $y_axis_interval) {
    $y = $img_height - $padding - $y_margin - ($i - $min_value) * $plot_height / $max_range;
    imagestring($image, 4, $padding, $y - 8, floor($i*10)/10, $label_color);
    imageline($image, $padding + $x_margin - 5, $y, $padding + $x_margin, $y, $axis_color);
    imageline($image, $padding + $x_margin, $y, $padding + $x_margin + $plot_width, $y, $sub_color);
}

// Draw x-axis labels
$x_label_interval = ceil(count($years) / 10);
for ($i = 0; $i < count($years); $i += $x_label_interval) {
    $x = $padding + $x_margin + $i * $plot_width / (count($years) - 1);
    imagestring($image, 4, $x - 10, $img_height - $padding - 20, $years[$i], $label_color);
    imageline($image, $x, $img_height -$padding -20, $x, $img_height - $padding - $y_margin, $axis_color);
}
// Draw line graphs
imagesetthickness($image, 2);
imageantialias($image, true);
for ($i = 0; $i < count($line_colors); $i++) {
    $points = array();
    foreach ($data[$i] as $key => $value) {
        $x = $padding + $x_margin + $key * $plot_width / (count($years) - 1);
        $y = $img_height - $padding - $y_margin - ($value - $min_value) * $plot_height / $max_range;
        $points[] = $x;
        $points[] = $y;
    }
    imageopenpolygon($image, $points, count($data[$i]), $line_colors[$i]);
}

// Draw legends
$legend_padding = 10;
$legend_width = 20;
$legend_height = 10;
$legend_x = $img_width - $padding - $x_margin;
$legend_y = $padding + $y_margin + $legend_padding;
foreach ($legend_colors as $key => $value) {
    imagefilledrectangle($image, $legend_x, $legend_y + $key * ($legend_height + $legend_padding), $legend_x + $legend_width, $legend_y + $key * ($legend_height + $legend_padding) + $legend_height, $line_colors[$key]);
    
    imagestring($image, 4, $legend_x + $legend_width + $legend_padding, $legend_y + $key * ($legend_height + $legend_padding), $value, $label_color);
}

// Draw title
imagestring($image, 5, ( $img_width / 2 - $padding ), $padding, $title_id, $title_color);

// Output image
header('Content-type: image/png');
imagepng($image);

// Free memory
imagedestroy($image);

// check elapse time;
$time_elapsed = microtime(true) - $time_start;
error_log("=========Elapse time: $time_elapsed");
?>