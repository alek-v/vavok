<?php 
// (c) vavok.net
// modified: 4.7.2015. 12:41:02
require_once"../include/startup.php";
$uz = check($_GET['uz']);

if (file_exists(BASEDIR . "used/dataphoto/" . $uz . ".jpg")) {
    $filename = BASEDIR . "used/dataphoto/" . $uz . ".jpg";
} elseif (file_exists(BASEDIR . "used/dataphoto/" . $uz . ".png")) {
    $filename = BASEDIR . "used/dataphoto/" . $uz . ".png";
} elseif (file_exists(BASEDIR . "used/dataphoto/" . $uz . ".gif")) {
    $filename = BASEDIR . "used/dataphoto/" . $uz . ".gif";
} elseif (file_exists(BASEDIR . "used/dataphoto/" . $uz . ".jpeg")) {
    $filename = BASEDIR . "used/dataphoto/" . $uz . ".jpeg";
} 

$ext = substr($filename, strrpos($filename, '.') + 1);
$filename = file_get_contents($filename);

header('Content-Disposition: inline; filename="' . $uz . '"');
header("Content-type: image/" . $ext . "");
echo $filename;

?>