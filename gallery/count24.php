<?php
require_once"../include/startup.php";

$p24_hits = $vavok->get_data_file('datacounter/24_hits.dat');
$p24_hitsarr = explode("|", $p24_hits[0]);

foreach($p24_hitsarr as $k) {
    $p24datadays = explode("-", $k);
    $hour_data[] = $p24datadays[1];
} 

$p24_host = $vavok->get_data_file('datacounter/24_host.dat');
$p24_hostarr = explode("|", $p24_host[0]);

foreach($p24_hostarr as $k) {
    $p24datadays = explode("-", $k);
    $hour_host[] = $p24datadays[1];
} 

$max = 0;
$max_index = 0;
foreach ($hour_data as $index => $value) {
    if ($value > $max) {
        $max = $value;
        $max_index = $index;
    } 
} 

if ($max == 0) {
    $max = 1;
} 

$per_hit = array();
foreach ($hour_data as $value) {
    $per_hit[] = $value * 0.90 / $max;
} 

$per_host = array();
foreach ($hour_host as $value) {
    $per_host[] = $value * 0.90 / $max;
} 
$img = @imageCreateFromGIF(BASEDIR . 'images/img/counter24.gif');

$color1 = imageColorAllocate($img, 44, 191, 228);
$color2 = imageColorAllocate($img, 0, 0, 120);
$color_red = imageColorAllocate($img, 200, 0, 0);

$imageH = 96;
$imageW = 47;
$collW = 4;
$x1 = 114;
$y1 = (int)($imageW - $imageW * $per_hit[0] + 7);
$y1_host = (int)($imageW - $imageW * $per_host[0] + 7);
$x2 = $x1 - 3;

$counth = count($hour_data);
if ($counth > 24) {
    $counth = 24;
} 

for($i = 1;$i < $counth;$i++) {

    $y2 = (int)($imageW - $imageW * $per_hit[$i] + 7);
    imageLine($img, $x1, $y1, $x2, $y2, $color1); 

    $y2_host = (int)($imageW - $imageW * $per_host[$i] + 7);
    imageLine($img, $x1, $y1_host, $x2, $y2_host, $color2);
    if ($hour_data[$i] != 0 && $i == $max_index) {
        ImageString($img, 1, $x2-17, $y2-10, "max", $color_red);
        ImageString($img, 1, $x2 + 2, $y2-10, $hour_data[$i], $color2);

        imageLine($img, $x2-1, $y2-7, $x2-1, $y2 + 42, $color_red);
    } 
    $y1 = $y2;
    $y1_host = $y2_host;
    $x1 -= $collW;
    $x2 -= $collW;
} 
Header("Content-type: image/gif");
ImageGIF($img);
ImageDestroy($img);

?>