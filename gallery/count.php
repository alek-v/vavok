<?php
require_once '../include/startup.php';

$pcounter_guest = $vavok->go('db')->count_row('online', 'user = 0');

$pcounter_online = $vavok->go('db')->count_row('online');

$substr_count = $pcounter_guest;
$counter_online = $pcounter_online;
$counter_reg = $counter_online - $substr_count;

$img = @imageCreateFromGIF(BASEDIR . 'themes/images/img/counter.gif');
$color = imagecolorallocate($img, 169, 169, 169);
$color2 = imagecolorallocate($img, 102, 102, 102);

if ($counter_online >= 0 && $counter_online < 10) $pos = 66;
if ($counter_online >= 10 && $counter_online < 100) $pos = 58;
if ($counter_online >= 100 && $counter_online < 1000) $pos = 48;

ImageString($img, 6, $pos, 8, $counter_online, $color2);

Header("Content-type: image/gif");
ImageGIF($img);
ImageDestroy($img);

?>