<?php
require_once"../include/strtup.php";

$rat = (int)$_GET['rat'];
$imgs = (int)$_GET['imgs'];
$limit = (int)$_GET['limit'];

$rats = $rat;
if ($rat >= 100) {
    $rats = 99;
} 
if ($rat > 100) {
    $rat = 100;
} 
if ($limit == "") {
    $limit = 50;
} 
if ($limit > 100) {
    $limit = 100;
} 

if ($imgs == 1) {
    header("Content-type: image/gif");
    $im = imageCreateFromGIF("../images/img/grafic.gif"); 
    // -----------------------------------------------------------//
    $color = imagecolorallocate($im, 234, 237, 237);
    $color2 = imagecolorallocate($im, 227, 222, 222);
    $color3 = imagecolorallocate($im, 204, 200, 200);
    $color4 = imagecolorallocate($im, 185, 181, 181);
    $color5 = imagecolorallocate($im, 197, 195, 195);

    imagefilledrectangle ($im, 2, 1, 99, 2, $color);
    imagefilledrectangle ($im, 1, 3, 100, 4, $color2);
    imagefilledrectangle ($im, 1, 5, 100, 6, $color3);
    imagefilledrectangle ($im, 1, 7, 100, 8, $color4);
    imagefilledrectangle ($im, 2, 9, 99, 10, $color5); 
    // -----------------------------------------------------------//
    $color = imagecolorallocate($im, 255, 204, 204);
    $color2 = imagecolorallocate($im, 255, 153, 153);
    $color3 = imagecolorallocate($im, 255, 102, 102);
    $color4 = imagecolorallocate($im, 255, 51, 51);
    $color5 = imagecolorallocate($im, 255, 102, 102);
    $color6 = imagecolorallocate($im, 0, 0, 0);

    if ($rat > 0) {
        imagefilledrectangle ($im, 2, 1, $rats, 2, $color);
        imagefilledrectangle ($im, 1, 3, $rat, 4, $color2);
        imagefilledrectangle ($im, 1, 5, $rat, 6, $color3);
        imagefilledrectangle ($im, 1, 7, $rat, 8, $color4);
        imagefilledrectangle ($im, 2, 9, $rats, 10, $color5);
    } 

    ImageString($im, 1, 78, 2, "$rat%", $color6);

    ImageGIF($im);
} 

if ($imgs == 2) {
    header("Content-type: image/gif");
    $im = imageCreateFromGIF("../images/img/grafic.gif"); 
    // -----------------------------------------------------------//
    $color = imagecolorallocate($im, 234, 237, 237);
    $color2 = imagecolorallocate($im, 227, 222, 222);
    $color3 = imagecolorallocate($im, 204, 200, 200);
    $color4 = imagecolorallocate($im, 185, 181, 181);
    $color5 = imagecolorallocate($im, 197, 195, 195);

    imagefilledrectangle ($im, 2, 1, 99, 2, $color);
    imagefilledrectangle ($im, 1, 3, 100, 4, $color2);
    imagefilledrectangle ($im, 1, 5, 100, 6, $color3);
    imagefilledrectangle ($im, 1, 7, 100, 8, $color4);
    imagefilledrectangle ($im, 2, 9, 99, 10, $color5); 
    // -----------------------------------------------------------//
    $colorfon = imagecolorallocate($im, 218, 219, 219);
    $colorfon2 = imagecolorallocate($im, 197, 198, 198);
    $colorfon3 = imagecolorallocate($im, 175, 176, 176);
    $colorfon4 = imagecolorallocate($im, 153, 154, 154);
    $colorfon5 = imagecolorallocate($im, 175, 176, 176);

    imagefilledrectangle ($im, 2, 1, $limit, 2, $colorfon);
    imagefilledrectangle ($im, 1, 3, $limit, 4, $colorfon2);
    imagefilledrectangle ($im, 1, 5, $limit, 6, $colorfon3);
    imagefilledrectangle ($im, 1, 7, $limit, 8, $colorfon4);
    imagefilledrectangle ($im, 2, 9, $limit, 10, $colorfon5); 
    // -----------------------------------------------------------//
    $color = imagecolorallocate($im, 201, 201, 253);
    $color2 = imagecolorallocate($im, 153, 153, 255);
    $color3 = imagecolorallocate($im, 102, 102, 255);
    $color4 = imagecolorallocate($im, 51, 51, 255);
    $color5 = imagecolorallocate($im, 102, 102, 255);
    $color6 = imagecolorallocate($im, 0, 0, 0);

    if ($rat > 0) {
        imagefilledrectangle ($im, 2, 1, $rats, 2, $color);
        imagefilledrectangle ($im, 1, 3, $rat, 4, $color2);
        imagefilledrectangle ($im, 1, 5, $rat, 6, $color3);
        imagefilledrectangle ($im, 1, 7, $rat, 8, $color4);
        imagefilledrectangle ($im, 2, 9, $rats, 10, $color5);
    } 

    ImageString($im, 1, 78, 2, "$rat%", $color6);
    ImageGIF($im);
} 

if ($imgs == 3) {
    header("Content-type: image/gif");
    $im = imageCreateFromGIF("../images/img/grafic.gif"); 
    // -----------------------------------------------------------//
    $color = imagecolorallocate($im, 234, 237, 237);
    $color2 = imagecolorallocate($im, 227, 222, 222);
    $color3 = imagecolorallocate($im, 204, 200, 200);
    $color4 = imagecolorallocate($im, 185, 181, 181);
    $color5 = imagecolorallocate($im, 197, 195, 195);

    imagefilledrectangle ($im, 2, 1, 99, 2, $color);
    imagefilledrectangle ($im, 1, 3, 100, 4, $color2);
    imagefilledrectangle ($im, 1, 5, 100, 6, $color3);
    imagefilledrectangle ($im, 1, 7, 100, 8, $color4);
    imagefilledrectangle ($im, 2, 9, 99, 10, $color5); 
    // -----------------------------------------------------------//
    $color = imagecolorallocate($im, 204, 255, 204);
    $color2 = imagecolorallocate($im, 153, 255, 153);
    $color3 = imagecolorallocate($im, 102, 255, 102);
    $color4 = imagecolorallocate($im, 0, 255, 0);
    $color5 = imagecolorallocate($im, 102, 255, 102);
    $color6 = imagecolorallocate($im, 0, 0, 0);

    if ($rat > 0) {
        imagefilledrectangle ($im, 2, 1, $rats, 2, $color);
        imagefilledrectangle ($im, 1, 3, $rat, 4, $color2);
        imagefilledrectangle ($im, 1, 5, $rat, 6, $color3);
        imagefilledrectangle ($im, 1, 7, $rat, 8, $color4);
        imagefilledrectangle ($im, 2, 9, $rats, 10, $color5);
    } 

    ImageString($im, 1, 78, 2, "$rat%", $color6);
    ImageGIF($im);
} 

/* if($imgs==3){	
    header("Content-type: image/gif");
    $im = ImageCreate (102, 12);
        
    $color = ImageColorAllocate ($im, 239, 244, 245);
     $color2 = imagecolorallocate($im, 51, 255, 0); 
     $color3 = imagecolorallocate($im, 153, 153, 153); 
     
    imagefilledrectangle ($im, 1, 1, $rat, 10, $color2);
    imagerectangle ($im, 0, 0, 101, 11, $color3);
    ImageGIF($im); 
} */

?>