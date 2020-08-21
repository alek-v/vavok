<?php
// modified: 21.08.2020. 22:59:28
require_once"../include/startup.php";


//
 $on_guests = "SELECT * FROM online WHERE user = 0";
 $on_guests = @mysql_query($on_guests) or die(mysql_error());
 $pcounter_guest = mysql_num_rows($on_guests);
    
 $on_total = "SELECT * FROM online";
 $on_total = @mysql_query($on_total) or die(mysql_error());
 $pcounter_online = mysql_num_rows($on_total);
//
$substr_count = $pcounter_guest;
$counter_online = $pcounter_online;
$counter_reg = $counter_online - $substr_count;

$count = $vavok->get_data_file('datacounter/host.dat');
$count = explode("#", $count[0]);
$counter_host = $count[1];
$counter_all = $count[2];

$hcount = $vavok->get_data_file('datacounter/hits.dat');
$hcount = explode("#", $hcount[0]);
$counter_hits = $hcount[1];
$counter_allhits = $hcount[2];

$img = @imageCreateFromGIF(BASEDIR . 'images/img/counter.gif');
$color = imagecolorallocate($img, 169, 169, 169);
$color2 = imagecolorallocate($img, 102, 102, 102);

if ($counter_online >= 0 && $counter_online < 10) $pos = 66;
if ($counter_online >= 10 && $counter_online < 100) $pos = 58;
if ($counter_online >= 100 && $counter_online < 1000) $pos = 48;

ImageString($img, 1, 4, 8, $counter_host, $color);
ImageString($img, 1, 4, 15, $counter_hits, $color);
ImageString($img, 6, $pos, 8, $counter_online, $color2);

Header("Content-type: image/gif");
ImageGIF($img);
ImageDestroy($img);

?>