<?php
// Create image resource
$image = imagecreatetruecolor(400, 400);
// Set colors
$bg_color = imagecolorallocate($image, 255, 255, 255);
$rect_color = imagecolorallocate($image, 255, 0, 0);
$ellipse_color = imagecolorallocate($image, 0, 0, 255);
// Draw background
imagefilledrectangle($image, 0, 0, 400, 400, $bg_color);
// Draw rectangle
imagerectangle($image, 50, 50, 350, 350, $rect_color);
// Draw ellipse
imageellipse($image, 200, 200, 300, 200, $ellipse_color);
// Output image
header('Content-type: image/png');
imagepng($image);
// Free memory
imagedestroy($image);
?>